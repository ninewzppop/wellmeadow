<?php

namespace Tests\Feature;

use App\Models\Patient;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PatientTest extends TestCase
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

    protected function validPayload(array $overrides = []): array
    {
        return array_merge([
            'FirstName' => 'John',
            'LastName' => 'Doe',
            'DateReg' => '2026-08-26',
        ], $overrides);
    }

    public function test_store_generates_pt1_when_table_is_empty(): void
    {
        $response = $this->post('/patients', $this->validPayload());

        $response->assertRedirect('/patients');

        $this->assertDatabaseHas('Patient', [
            'Pt_No' => 'PT1',
            'FirstName' => 'John',
            'LastName' => 'Doe',
        ]);
    }

    public function test_store_generates_sequential_numbers(): void
    {
        Patient::create(['Pt_No' => 'PT1', 'FirstName' => 'One', 'LastName' => 'Patient']);
        Patient::create(['Pt_No' => 'PT2', 'FirstName' => 'Two', 'LastName' => 'Patient']);

        $response = $this->post('/patients', $this->validPayload(['LastName' => 'New']));

        $response->assertRedirect('/patients');

        $this->assertDatabaseHas('Patient', [
            'Pt_No' => 'PT3',
            'LastName' => 'New',
        ]);
    }

    public function test_store_continues_from_highest_existing_number(): void
    {
        Patient::create(['Pt_No' => 'PT7', 'FirstName' => 'Seven', 'LastName' => 'Patient']);

        $response = $this->post('/patients', $this->validPayload());

        $response->assertRedirect('/patients');

        $this->assertDatabaseHas('Patient', ['Pt_No' => 'PT8']);
    }

    public function test_store_treats_legacy_zero_padded_codes_numerically(): void
    {
        Patient::create(['Pt_No' => 'PT003', 'FirstName' => 'Legacy', 'LastName' => 'Patient']);
        Patient::create(['Pt_No' => 'PT010', 'FirstName' => 'Legacy', 'LastName' => 'Patient']);

        $response = $this->post('/patients', $this->validPayload());

        $response->assertRedirect('/patients');

        $this->assertDatabaseHas('Patient', ['Pt_No' => 'PT11']);
    }

    public function test_create_page_previews_next_patient_no(): void
    {
        Patient::create(['Pt_No' => 'PT4', 'FirstName' => 'Four', 'LastName' => 'Patient']);

        $response = $this->get('/patients/create');

        $response->assertOk();
        $response->assertSee('value="PT5"', false);
    }

    public function test_update_does_not_change_patient_no(): void
    {
        $patient = Patient::create(['Pt_No' => 'PT5', 'FirstName' => 'Old', 'LastName' => 'Name']);

        $response = $this->put("/patients/{$patient->Pt_No}", $this->validPayload([
            'FirstName' => 'Renamed',
        ]));

        $response->assertRedirect('/patients');

        $this->assertDatabaseHas('Patient', [
            'Pt_No' => 'PT5',
            'FirstName' => 'Renamed',
        ]);

        $this->assertDatabaseMissing('Patient', ['Pt_No' => 'PT6']);
    }
}
