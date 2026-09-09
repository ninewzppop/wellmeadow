<?php

namespace Tests\Feature;

use App\Models\Patient;
use App\Models\PatientAllergy;
use App\Models\Pharmaceutical;
use App\Models\Stf;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AllergyTest extends TestCase
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

    public function test_allergy_record_can_be_created(): void
    {
        $patient = Patient::create(['Pt_No' => 'PT001', 'FirstName' => 'John', 'LastName' => 'Doe']);
        $drug = Pharmaceutical::create(['Drug_No' => 'DR01', 'Name' => 'Penicillin']);
        $staff = Stf::create(['Stf_No' => 'S1001', 'FirstName' => 'Nurse', 'LastName' => 'Joy']);

        $response = $this->post('/allergies', [
            'Pt_No' => $patient->Pt_No,
            'Drug_No' => $drug->Drug_No,
            'Reaction' => 'Rash',
            'Severity' => 'Moderate',
            'DiagDate' => '2026-08-01',
            'Rec_Stf_No' => $staff->Stf_No,
        ]);

        $response->assertRedirect('/allergies');

        $this->assertDatabaseHas('PatientAllergy', [
            'Pt_No' => 'PT001',
            'Drug_No' => 'DR01',
            'Reaction' => 'Rash',
            'Severity' => 'Moderate',
        ]);
    }

    public function test_allergy_record_can_be_updated(): void
    {
        PatientAllergy::create([
            'Allergy_No' => 'AL02',
            'Allergy_Name' => 'Aspirin',
            'Reaction' => 'Hives',
            'Severity' => 'Mild',
            'DiagDate' => '2026-07-01',
        ]);

        $this->put('/allergies/AL02', [
            'Allergy_No' => 'AL02',
            'Allergy_Name' => 'Aspirin',
            'Reaction' => 'Swelling',
            'Severity' => 'Severe',
            'DiagDate' => '2026-07-02',
        ])->assertRedirect('/allergies');

        $this->assertDatabaseHas('PatientAllergy', [
            'Allergy_No' => 'AL02',
            'Reaction' => 'Swelling',
            'Severity' => 'Severe',
        ]);
    }

    public function test_allergy_record_can_be_deleted(): void
    {
        PatientAllergy::create([
            'Allergy_No' => 'AL03',
            'Reaction' => 'Itching',
            'Severity' => 'Mild',
            'DiagDate' => '2026-06-15',
        ]);

        $this->delete('/allergies/AL03')->assertRedirect('/allergies');

        $this->assertDatabaseMissing('PatientAllergy', ['Allergy_No' => 'AL03']);
    }

    public function test_index_filters_by_severity_and_search(): void
    {
        $patient = Patient::create(['Pt_No' => 'PT002', 'FirstName' => 'Jane', 'LastName' => 'Roe']);
        PatientAllergy::create([
            'Allergy_No' => 'AL10',
            'Pt_No' => $patient->Pt_No,
            'Allergy_Name' => 'Peanuts',
            'Reaction' => 'Anaphylaxis',
            'Severity' => 'Severe',
            'DiagDate' => '2026-05-01',
        ]);
        PatientAllergy::create([
            'Allergy_No' => 'AL11',
            'Allergy_Name' => 'Dust',
            'Reaction' => 'Sneezing',
            'Severity' => 'Mild',
            'DiagDate' => '2026-05-02',
        ]);

        $this->get('/allergies?severity=Severe')
            ->assertOk()
            ->assertSee('Peanuts')
            ->assertDontSee('Sneezing');

        $this->get('/allergies?search=Jane')
            ->assertOk()
            ->assertSee('Peanuts')
            ->assertDontSee('Sneezing');
    }
}
