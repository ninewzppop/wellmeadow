<?php

namespace Tests\Feature;

use App\Models\InPatient;
use App\Models\Patient;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InPatientTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->actingAs(User::create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password123',
            'role' => 'medical_director',
        ]));
    }

    protected function createPatient(): Patient
    {
        return Patient::create(['Pt_No' => 'PT1', 'FirstName' => 'John', 'LastName' => 'Doe']);
    }

    protected function validPayload(array $overrides = []): array
    {
        return array_merge([
            'Pt_No' => 'PT1',
            'DateWaitList' => '2026-08-26',
        ], $overrides);
    }

    public function test_store_generates_ip1_when_table_is_empty(): void
    {
        $this->createPatient();

        $response = $this->post('/in-patients', $this->validPayload());

        $response->assertRedirect('/in-patients');

        $this->assertDatabaseHas('InPatient', [
            'In_Pt_No' => 'IP1',
            'Pt_No' => 'PT1',
        ]);
    }

    public function test_store_continues_from_highest_existing_number(): void
    {
        $this->createPatient();
        InPatient::create(['In_Pt_No' => 'IP5', 'Pt_No' => 'PT1']);

        $response = $this->post('/in-patients', $this->validPayload(['DateWaitList' => '2026-08-27']));

        $response->assertRedirect('/in-patients');

        $this->assertDatabaseHas('InPatient', ['In_Pt_No' => 'IP6']);
        $record = InPatient::where('In_Pt_No', 'IP6')->first();
        $this->assertNotNull($record);
        $this->assertEquals('2026-08-27', $record->DateWaitList->format('Y-m-d'));
    }

    public function test_store_treats_legacy_zero_padded_codes_numerically(): void
    {
        $this->createPatient();
        InPatient::create(['In_Pt_No' => 'IP003', 'Pt_No' => 'PT1']);
        InPatient::create(['In_Pt_No' => 'IP010', 'Pt_No' => 'PT1']);

        $response = $this->post('/in-patients', $this->validPayload());

        $response->assertRedirect('/in-patients');

        $this->assertDatabaseHas('InPatient', ['In_Pt_No' => 'IP11']);
    }

    public function test_create_page_hides_auto_generated_admission_no(): void
    {
        InPatient::create(['In_Pt_No' => 'IP4', 'Pt_No' => $this->createPatient()->Pt_No]);

        $response = $this->get('/in-patients/create');

        $response->assertOk();
        $response->assertDontSee('value="IP5"', false);
        $response->assertDontSee(__('Admission No.'));
        $response->assertDontSee(__('Auto-generated'));
    }

    public function test_update_does_not_change_admission_no(): void
    {
        $this->createPatient();
        $inPatient = InPatient::create(['In_Pt_No' => 'IP2', 'Pt_No' => 'PT1']);

        $response = $this->put("/in-patients/{$inPatient->In_Pt_No}", $this->validPayload([
            'DateWaitList' => '2026-08-28',
        ]));

        $response->assertRedirect('/in-patients');

        $this->assertDatabaseHas('InPatient', [
            'In_Pt_No' => 'IP2',
        ]);
        $record = InPatient::where('In_Pt_No', 'IP2')->first();
        $this->assertEquals('2026-08-28', $record->DateWaitList->format('Y-m-d'));

        $this->assertDatabaseMissing('InPatient', ['In_Pt_No' => 'IP3']);
    }
}
