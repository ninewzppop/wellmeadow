<?php

namespace Tests\Feature;

use App\Http\Controllers\DashboardController;
use App\Models\Bed;
use App\Models\CentralStock;
use App\Models\InPatient;
use App\Models\Patient;
use App\Models\PatientAllergy;
use App\Models\Pharmaceutical;
use App\Models\Stf;
use App\Models\StfRota;
use App\Models\User;
use App\Models\Wd;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardWidgetsTest extends TestCase
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

    public function test_allergy_widget_calculates_coverage_and_severe_count(): void
    {
        // 4 patients, 2 have allergies, 1 severe
        Patient::create(['Pt_No' => 'PT1', 'FirstName' => 'A', 'LastName' => 'One']);
        Patient::create(['Pt_No' => 'PT2', 'FirstName' => 'B', 'LastName' => 'Two']);
        Patient::create(['Pt_No' => 'PT3', 'FirstName' => 'C', 'LastName' => 'Three']);
        Patient::create(['Pt_No' => 'PT4', 'FirstName' => 'D', 'LastName' => 'Four']);

        PatientAllergy::create(['Allergy_No' => 'AL1', 'Pt_No' => 'PT1', 'Allergy_Name' => 'Penicillin', 'Reaction' => 'Rash', 'Severity' => 'Mild', 'DiagDate' => now()->toDateString()]);
        PatientAllergy::create(['Allergy_No' => 'AL2', 'Pt_No' => 'PT2', 'Allergy_Name' => 'Latex', 'Reaction' => 'Anaphylaxis', 'Severity' => 'Severe', 'DiagDate' => now()->toDateString()]);
        // PT2 also has another mild allergy - distinct patient count should still be 2
        PatientAllergy::create(['Allergy_No' => 'AL3', 'Pt_No' => 'PT2', 'Allergy_Name' => 'Peanut', 'Reaction' => 'Swelling', 'Severity' => 'Moderate', 'DiagDate' => now()->toDateString()]);

        $response = $this->get('/');
        $response->assertOk();
        $data = $response->viewData('allergyCoverage') !== null ? $response->original->getData() : [];

        // Use viewData via original
        $viewData = $response->original->getData();
        $this->assertEquals(50.0, $viewData['allergyCoverage']); // 2 / 4 *100
        $this->assertEquals(2, $viewData['allergyPatientCount']);
        $this->assertEquals(4, $viewData['totalPatients']);
        // severe count is record count, not distinct
        $this->assertEquals(1, $viewData['severeAllergyCount']);
        $this->assertEquals(1, $viewData['severePatientCount']);

        $response->assertSee('Allergy coverage');
        $response->assertSee('50%');
    }

    public function test_allergy_widget_zero_patients_and_zero_allergies(): void
    {
        // No patients
        $response = $this->get('/');
        $viewData = $response->original->getData();
        $this->assertEquals(0, $viewData['allergyCoverage']);
        $this->assertEquals(0, $viewData['severeAllergyCount']);

        // Patients but no allergies
        Patient::create(['Pt_No' => 'PT1', 'FirstName' => 'A', 'LastName' => 'One']);
        $response2 = $this->get('/');
        $viewData2 = $response2->original->getData();
        $this->assertEquals(0, $viewData2['allergyCoverage']);
        $this->assertEquals(0, $viewData2['severeAllergyCount']);
    }

    public function test_staff_shift_breakdown_sums_to_total_and_threshold(): void
    {
        $weekStart = today()->startOfWeek()->toDateString();
        Wd::create(['Wd_No' => 'WD01', 'Wd_Name' => 'A', 'TotalBeds' => 10]);
        // Create 3 staff, one per shift
        Stf::create(['Stf_No' => 'S1', 'FirstName' => 'Morning', 'LastName' => 'One']);
        Stf::create(['Stf_No' => 'S2', 'FirstName' => 'Evening', 'LastName' => 'Two']);
        Stf::create(['Stf_No' => 'S3', 'FirstName' => 'Night', 'LastName' => 'Three']);
        StfRota::create(['StfRota_No' => 'R1', 'Stf_No' => 'S1', 'Wd_No' => 'WD01', 'WkBegin' => $weekStart, 'Shift' => 'Morning']);
        StfRota::create(['StfRota_No' => 'R2', 'Stf_No' => 'S2', 'Wd_No' => 'WD01', 'WkBegin' => $weekStart, 'Shift' => 'Evening']);
        StfRota::create(['StfRota_No' => 'R3', 'Stf_No' => 'S3', 'Wd_No' => 'WD01', 'WkBegin' => $weekStart, 'Shift' => 'Night']);

        $response = $this->get('/');
        $viewData = $response->original->getData();

        $breakdown = $viewData['staffShiftBreakdown'];
        $total = $viewData['staffOnDuty'];
        $threshold = $viewData['staffLowThreshold'];

        $this->assertEquals(1, $breakdown['Morning']);
        $this->assertEquals(1, $breakdown['Evening']);
        $this->assertEquals(1, $breakdown['Night']);
        $this->assertEquals(3, $total);
        $this->assertEquals(DashboardController::STAFF_LOW_THRESHOLD, $threshold);
        $this->assertEquals(2, $threshold);
        // sanity check: sum shifts == total when each staff works single shift
        $sum = collect(['Morning','Evening','Night'])->sum(fn($s)=> $breakdown[$s] ?? 0);
        $this->assertEquals($total, $sum);

        // threshold highlight: if count <2 should be low
        $this->assertTrue(($breakdown['Morning'] < $threshold) === true); // 1 <2
        $response->assertSee('Morning');
        $response->assertSee('Evening');
        $response->assertSee('Night');
    }

    public function test_reorder_widget_split_into_three_groups_no_mix(): void
    {
        // Surgical low
        CentralStock::create(['Item_No' => 'IT01', 'Name' => 'Scalpel', 'ItemType' => 'surgical', 'QtyInStock' => 2, 'ReorderLvl' => 5]);
        // Non-surgical low
        CentralStock::create(['Item_No' => 'IT02', 'Name' => 'Bandage', 'ItemType' => 'non-surgical', 'QtyInStock' => 1, 'ReorderLvl' => 5]);
        // Normal stock (should not appear)
        CentralStock::create(['Item_No' => 'IT03', 'Name' => 'Gloves', 'ItemType' => 'surgical', 'QtyInStock' => 10, 'ReorderLvl' => 5]);
        // Pharmaceutical low
        Pharmaceutical::create(['Drug_No' => 'DR01', 'Name' => 'Aspirin', 'QtyInStock' => 1, 'ReorderLvl' => 5]);

        $response = $this->get('/');
        $viewData = $response->original->getData();

        $surgical = $viewData['surgicalAlerts'];
        $nonSurgical = $viewData['nonSurgicalAlerts'];
        $lowDrugs = $viewData['lowDrugs'];

        $this->assertCount(1, $surgical);
        $this->assertEquals('IT01', $surgical->first()->Item_No);
        $this->assertTrue($surgical->every(fn($i)=> $i->ItemType === 'surgical'));

        $this->assertCount(1, $nonSurgical);
        $this->assertEquals('IT02', $nonSurgical->first()->Item_No);
        $this->assertTrue($nonSurgical->every(fn($i)=> $i->ItemType === 'non-surgical'));

        $this->assertCount(1, $lowDrugs);
        $this->assertEquals('DR01', $lowDrugs->first()->Drug_No);

        // Ensure no overlap and no missing
        $this->assertFalse($surgical->pluck('Item_No')->contains('IT02'));
        $this->assertFalse($nonSurgical->pluck('Item_No')->contains('IT01'));
        $this->assertFalse($surgical->pluck('Item_No')->contains('IT03'));
        $this->assertFalse($nonSurgical->pluck('Item_No')->contains('IT03'));

        $response->assertSee('Surgical supply');
        $response->assertSee('Non-surgical supply');
        $response->assertSee('Pharmaceutical');
    }

    public function test_ward_filter_affects_ward_related_widgets_but_not_global(): void
    {
        Wd::create(['Wd_No' => 'WD01', 'Wd_Name' => 'Ward A', 'TotalBeds' => 5]);
        Wd::create(['Wd_No' => 'WD02', 'Wd_Name' => 'Ward B', 'TotalBeds' => 5]);
        Bed::create(['Bed_No' => 'B01', 'Wd_No' => 'WD01', 'BedStatus' => 'Occupied']);
        Bed::create(['Bed_No' => 'B02', 'Wd_No' => 'WD01', 'BedStatus' => 'Available']);
        Bed::create(['Bed_No' => 'B03', 'Wd_No' => 'WD02', 'BedStatus' => 'Occupied']);
        Bed::create(['Bed_No' => 'B04', 'Wd_No' => 'WD02', 'BedStatus' => 'Occupied']);

        // Staff
        $weekStart = today()->startOfWeek()->toDateString();
        Stf::create(['Stf_No' => 'SA', 'FirstName' => 'A', 'LastName' => 'Staff']);
        Stf::create(['Stf_No' => 'SB', 'FirstName' => 'B', 'LastName' => 'Staff']);
        StfRota::create(['StfRota_No' => 'R1', 'Stf_No' => 'SA', 'Wd_No' => 'WD01', 'WkBegin' => $weekStart, 'Shift' => 'Morning']);
        StfRota::create(['StfRota_No' => 'R2', 'Stf_No' => 'SB', 'Wd_No' => 'WD02', 'WkBegin' => $weekStart, 'Shift' => 'Night']);

        // Overstay: patient in WD01 with bed B01, DatePlaced 10 days ago, ExpStay 2 days => overstay
        Patient::create(['Pt_No' => 'PT1', 'FirstName' => 'Over', 'LastName' => 'Stay']);
        InPatient::create(['In_Pt_No' => 'IP1', 'Pt_No' => 'PT1', 'Bed_No' => 'B01', 'DatePlaced' => today()->subDays(10)->toDateString(), 'ExpStayDays' => 2]);
        // Waiting for bed (no bed)
        Patient::create(['Pt_No' => 'PT2', 'FirstName' => 'Wait', 'LastName' => 'List']);
        InPatient::create(['In_Pt_No' => 'IP2', 'Pt_No' => 'PT2', 'Bed_No' => null, 'DateWaitList' => today()->toDateString()]);
        // Allergy
        Patient::create(['Pt_No' => 'PT3', 'FirstName' => 'Allergy', 'LastName' => 'Patient']);
        PatientAllergy::create(['Allergy_No' => 'AL1', 'Pt_No' => 'PT3', 'Allergy_Name' => 'Penicillin', 'Reaction' => 'Rash', 'Severity' => 'Mild', 'DiagDate' => now()->toDateString()]);

        // Without filter
        $respAll = $this->get('/');
        $dataAll = $respAll->original->getData();
        $this->assertEquals(4, $dataAll['totalBeds']); // total 4 beds
        $this->assertEquals(3, $dataAll['occupiedBeds']);
        // With ward filter WD01
        $respWard = $this->get('/?ward=WD01');
        $dataWard = $respWard->original->getData();

        // Bed availability filtered
        $this->assertEquals(2, $dataWard['totalBeds']); // only WD01 has 2 beds
        $this->assertEquals(1, $dataWard['occupiedBeds']);
        $this->assertCount(1, $dataWard['wardBedStats']);
        $this->assertEquals('WD01', $dataWard['wardBedStats']->first()['ward']->Wd_No);

        // Staff filtered
        $this->assertEquals(1, $dataWard['staffOnDuty']);
        $this->assertEquals(1, $dataWard['staffShiftBreakdown']['Morning']);
        $this->assertEquals(0, $dataWard['staffShiftBreakdown']['Night']);

        // Overstay filtered by ward (only B01 in WD01 is overstay)
        $this->assertEquals(1, $dataWard['overstayCount']);
        // Check that all-wards overstay is also 1 (since only one overstay)
        $this->assertEquals(1, $dataAll['overstayCount']);

        // Global widgets not filtered: Total patients and allergy should be same regardless of ward
        $this->assertEquals($dataAll['totalPatients'], $dataWard['totalPatients']);
        $this->assertEquals($dataAll['allergyPatientCount'], $dataWard['allergyPatientCount']);
        $this->assertEquals($dataAll['allergyCoverage'], $dataWard['allergyCoverage']);
    }

    public function test_last_updated_timestamp_present_and_formatted(): void
    {
        $response = $this->get('/');
        $viewData = $response->original->getData();
        $this->assertArrayHasKey('lastUpdated', $viewData);
        $this->assertNotNull($viewData['lastUpdated']);
        $this->assertTrue($viewData['lastUpdated'] instanceof \Illuminate\Support\Carbon);
        // Format check d/m/Y H:i:s
        $formatted = $viewData['lastUpdated']->format('d/m/Y H:i:s');
        $this->assertMatchesRegularExpression('/\d{2}\/\d{2}\/\d{4} \d{2}:\d{2}:\d{2}/', $formatted);
        $response->assertSee('Last updated');
        $response->assertSee(e($formatted) ?: $formatted);
    }
}
