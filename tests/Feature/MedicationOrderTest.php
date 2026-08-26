<?php

namespace Tests\Feature;

use App\Models\Appointment;
use App\Models\MedicationOrder;
use App\Models\Medications;
use App\Models\Patient;
use App\Models\PatientAllergy;
use App\Models\Pharmaceutical;
use App\Models\Room;
use App\Models\Stf;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MedicationOrderTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->actingAs(User::create(['name' => 'Test', 'email' => 'test@example.com', 'password' => 'x']));
    }

    private function fixtures(): array
    {
        $patient = Patient::create(['Pt_No' => 'PT1', 'FirstName' => 'John', 'LastName' => 'Doe']);
        $stf = Stf::create(['Stf_No' => 'S1001', 'FirstName' => 'Dr', 'LastName' => 'A']);
        $room = Room::create(['Room_No' => 'R001', 'RoomName' => 'Room 1']);
        Pharmaceutical::create(['Drug_No' => 'DR01', 'Name' => 'Paracetamol', 'UnitsPerDay' => 1, 'AdminMethod' => 'Oral', 'QtyInStock' => 100, 'ReorderLvl' => 10]);
        Pharmaceutical::create(['Drug_No' => 'DR02', 'Name' => 'Ibuprofen', 'UnitsPerDay' => 1, 'AdminMethod' => 'Oral', 'QtyInStock' => 100, 'ReorderLvl' => 10]);

        $appt = Appointment::create([
            'Appt_No' => 'A1', 'Pt_No' => 'PT1', 'Consult_Stf_No' => 'S1001',
            'ApptDate' => now()->toDateString(), 'ApptTime' => '09:00', 'Room_No' => 'R001', 'status' => Appointment::STATUS_IN_CONSULTATION,
        ]);

        return compact('patient', 'stf', 'room', 'appt');
    }

    private function payload(array $overrides = []): array
    {
        return array_merge([
            'drugs' => [
                ['Drug_No' => 'DR01', 'UnitsPerDay' => 2, 'AdminMethod' => 'Oral', 'StartDate' => now()->toDateString(), 'FinishDate' => now()->addDays(5)->toDateString()],
                ['Drug_No' => 'DR02', 'UnitsPerDay' => 1, 'AdminMethod' => 'Oral', 'StartDate' => now()->toDateString(), 'FinishDate' => now()->addDays(3)->toDateString()],
            ],
        ], $overrides);
    }

    public function test_doctor_can_send_multi_drug_order_to_queue(): void
    {
        ['room' => $room, 'appt' => $appt] = $this->fixtures();

        $res = $this->post(route('rooms.medication-order.store', [$room, $appt]), $this->payload());
        $res->assertRedirect();

        $this->assertDatabaseHas('MedicationOrder', ['Pt_No' => 'PT1', 'Appt_No' => 'A1', 'status' => 'pending']);
        $order = MedicationOrder::first();
        $this->assertCount(2, $order->items);
        // appointment stays in consultation (not auto-completed)
        $this->assertEquals(Appointment::STATUS_IN_CONSULTATION, $appt->fresh()->status);
        // not yet in Medications history
        $this->assertDatabaseMissing('Medications', ['Pt_No' => 'PT1']);
    }

    public function test_validation_requires_all_row_fields(): void
    {
        ['room' => $room, 'appt' => $appt] = $this->fixtures();

        $payload = ['drugs' => [['Drug_No' => 'DR01', 'UnitsPerDay' => 1, 'AdminMethod' => 'Oral', 'StartDate' => now()->toDateString()]]]; // missing FinishDate
        $res = $this->post(route('rooms.medication-order.store', [$room, $appt]), $payload);
        $res->assertSessionHasErrors('drugs.0.FinishDate');
        $this->assertDatabaseCount('MedicationOrder', 0);
    }

    public function test_allergy_blocks_without_override(): void
    {
        ['room' => $room, 'appt' => $appt] = $this->fixtures();
        PatientAllergy::create(['Allergy_No' => 'AL1', 'Pt_No' => 'PT1', 'Drug_No' => 'DR01', 'Allergy_Name' => 'Paracetamol', 'Reaction' => 'Rash', 'Severity' => 'Moderate', 'DiagDate' => now()->toDateString()]);

        $payload = ['drugs' => [['Drug_No' => 'DR01', 'UnitsPerDay' => 1, 'AdminMethod' => 'Oral', 'StartDate' => now()->toDateString(), 'FinishDate' => now()->toDateString()]]];
        $res = $this->post(route('rooms.medication-order.store', [$room, $appt]), $payload);
        $res->assertSessionHasErrors('drugs');
        $this->assertDatabaseCount('MedicationOrder', 0);

        // with override passes
        $res2 = $this->post(route('rooms.medication-order.store', [$room, $appt]), array_merge($payload, ['override_allergy' => '1']));
        $res2->assertRedirect();
        $this->assertDatabaseCount('MedicationOrder', 1);
    }

    public function test_confirm_copies_to_history_and_removes_from_queue(): void
    {
        ['room' => $room, 'appt' => $appt] = $this->fixtures();
        $this->post(route('rooms.medication-order.store', [$room, $appt]), $this->payload());
        $order = MedicationOrder::first();

        $res = $this->post(route('medications.confirm', $order));
        $res->assertRedirect(route('medications.index'));

        $this->assertDatabaseHas('MedicationOrder', ['Order_No' => $order->Order_No, 'status' => 'dispensed']);
        $this->assertDatabaseCount('Medications', 2);
        $this->assertDatabaseHas('Medications', ['Pt_No' => 'PT1', 'Drug_No' => 'DR01', 'UnitsPerDay' => 2]);
        $this->assertDatabaseHas('Medications', ['Pt_No' => 'PT1', 'Drug_No' => 'DR02']);

        // queue hides dispensed
        $this->get(route('medications.index'))->assertDontSee($order->Order_No);
    }

    public function test_cancel_requires_reason_and_removes_from_queue(): void
    {
        ['room' => $room, 'appt' => $appt] = $this->fixtures();
        $this->post(route('rooms.medication-order.store', [$room, $appt]), $this->payload());
        $order = MedicationOrder::first();

        $res = $this->post(route('medications.cancel', $order), []);
        $res->assertSessionHasErrors('CancelReason');

        $res2 = $this->post(route('medications.cancel', $order), ['CancelReason' => 'Patient declined']);
        $res2->assertRedirect(route('medications.index'));
        $this->assertDatabaseHas('MedicationOrder', ['Order_No' => $order->Order_No, 'status' => 'cancelled', 'CancelReason' => 'Patient declined']);
        $this->assertDatabaseCount('Medications', 0);
        $this->assertEquals(0, MedicationOrder::where('status', MedicationOrder::STATUS_PENDING)->count());
        $this->get(route('medications.index'))->assertOk()->assertSee(__('No pending medication orders.'));
    }

    public function test_queue_index_shows_pending_orders(): void
    {
        ['room' => $room, 'appt' => $appt] = $this->fixtures();
        $this->post(route('rooms.medication-order.store', [$room, $appt]), $this->payload());

        $this->get(route('medications.index'))->assertOk()->assertSee('PT1');
    }
}
