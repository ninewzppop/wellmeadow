<?php

namespace Tests\Feature;

use App\Models\Appointment;
use App\Models\Patient;
use App\Models\Room;
use App\Models\Stf;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AppointmentTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->actingAs(User::create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password123',
        ]));
    }

    protected function createFixtures(): void
    {
        Patient::create(['Pt_No' => 'PT1', 'FirstName' => 'John', 'LastName' => 'Doe']);
        Stf::create(['Stf_No' => 'S1001', 'FirstName' => 'Dr', 'LastName' => 'Who']);
        Room::create(['Room_No' => 'R001', 'RoomName' => 'Room 1']);
    }

    protected function validPayload(array $overrides = []): array
    {
        return array_merge([
            'Pt_No' => 'PT1',
            'Consult_Stf_No' => 'S1001',
            'ApptDate' => '2026-08-27',
            'ApptTime' => '09:00',
            'Room_No' => 'R001',
            'status' => Appointment::STATUS_SCHEDULED,
        ], $overrides);
    }

    public function test_store_generates_a1_when_table_is_empty(): void
    {
        $this->createFixtures();

        $response = $this->post('/appointments', $this->validPayload());

        $response->assertRedirect('/appointments');

        $this->assertDatabaseHas('Appointment', [
            'Appt_No' => 'A1',
            'Pt_No' => 'PT1',
            'Room_No' => 'R001',
        ]);
    }

    public function test_store_continues_from_highest_existing_number(): void
    {
        $this->createFixtures();
        Appointment::create($this->validPayload(['Appt_No' => 'A5']));

        $response = $this->post('/appointments', $this->validPayload(['ApptTime' => '10:00']));

        $response->assertRedirect('/appointments');

        $this->assertDatabaseHas('Appointment', ['Appt_No' => 'A6', 'ApptTime' => '10:00']);
    }

    public function test_store_treats_legacy_zero_padded_codes_numerically(): void
    {
        $this->createFixtures();
        Appointment::create($this->validPayload(['Appt_No' => 'A003']));
        Appointment::create($this->validPayload(['Appt_No' => 'A012']));

        $response = $this->post('/appointments', $this->validPayload(['ApptTime' => '11:00']));

        $response->assertRedirect('/appointments');

        $this->assertDatabaseHas('Appointment', ['Appt_No' => 'A13']);
    }

    public function test_create_page_previews_next_appointment_no(): void
    {
        $this->createFixtures();
        Appointment::create($this->validPayload(['Appt_No' => 'A4']));

        $response = $this->get('/appointments/create');

        $response->assertOk();
        $response->assertSee('value="A5"', false);
    }
}
