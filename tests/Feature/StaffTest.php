<?php

namespace Tests\Feature;

use App\Models\Pos;
use App\Models\Stf;
use App\Models\StfQual;
use App\Models\StfRota;
use App\Models\StfWorkExp;
use App\Models\User;
use App\Models\Wd;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StaffTest extends TestCase
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

    public function test_staff_can_be_created_with_nested_records(): void
    {
        Pos::create(['Pos_No' => 'P001', 'Pos_Name' => 'Registered Nurse', 'SalaryScale' => 'Band 5']);
        Wd::create(['Wd_No' => 'WD01', 'Wd_Name' => 'Cardiology', 'Location' => 'A1', 'TotalBeds' => 30, 'TelExtension' => '1']);

        $response = $this->post('/staff', [
            'FirstName' => 'John',
            'LastName' => 'Doe',
            'Address' => '1 Test Street',
            'TelNo' => '0117 555 9999',
            'DOB' => '1990-01-01',
            'Sex' => 'M',
            'NIN' => 'AA111111A',
            'Alloc_Wd_No' => 'WD01',
            'positions' => [
                ['Pos_No' => 'P001', 'CurrSalary' => 30000, 'HrsPerWk' => 37.5, 'ContractType' => 'Permanent', 'PaymentType' => 'Monthly'],
            ],
            'qualifications' => [
                ['Type' => 'BSc Nursing', 'QualDate' => '2012-06-01', 'Institution' => 'City University'],
            ],
            'work_experiences' => [
                ['Organization' => 'Local Hospital', 'Position' => 'Staff Nurse', 'StartDate' => '2013-01-01', 'FinishDate' => '2018-01-01'],
            ],
        ]);

        $response->assertRedirect('/staff');

        // Stf_No is auto-generated server-side (S1001, S1002, …).
        $staff = Stf::first();
        $this->assertNotNull($staff);
        $this->assertMatchesRegularExpression('/^S\d+$/', $staff->Stf_No);
        $this->assertDatabaseHas('Stf', ['Stf_No' => $staff->Stf_No, 'LastName' => 'Doe', 'Alloc_Wd_No' => 'WD01']);
        $this->assertDatabaseHas('StfQual', ['Stf_No' => $staff->Stf_No, 'Type' => 'BSc Nursing']);
        $this->assertDatabaseHas('StfWorkExp', ['Stf_No' => $staff->Stf_No, 'Organization' => 'Local Hospital']);
        $this->assertDatabaseHas('StfPos', ['Stf_No' => $staff->Stf_No, 'Pos_No' => 'P001']);
    }

    public function test_create_page_hides_auto_generated_staff_no(): void
    {
        $response = $this->get('/staff/create');

        $response->assertOk();
        $response->assertDontSee(__('Staff No *'));
    }

    public function test_staff_can_be_updated(): void
    {
        $staff = Stf::create(['Stf_No' => 'S2002', 'FirstName' => 'Jane', 'LastName' => 'Roe']);

        $this->put('/staff/S2002', [
            'FirstName' => 'Jane',
            'LastName' => 'Roe-Smith',
            'Address' => null,
            'TelNo' => null,
            'DOB' => null,
            'Sex' => null,
            'NIN' => null,
        ])->assertRedirect('/staff');

        $this->assertDatabaseHas('Stf', ['Stf_No' => 'S2002', 'LastName' => 'Roe-Smith']);
    }

    public function test_staff_with_children_can_be_deleted(): void
    {
        Stf::create(['Stf_No' => 'S2003', 'FirstName' => 'Deleted', 'LastName' => 'Person']);
        StfQual::create(['Qual_No' => 'Q3', 'Stf_No' => 'S2003', 'Type' => 'RGN']);
        StfWorkExp::create(['WorkExp_No' => 'E3', 'Stf_No' => 'S2003', 'Organization' => 'Somewhere']);
        Wd::create(['Wd_No' => 'WD97', 'Wd_Name' => 'Delete Ward', 'Location' => 'X', 'TotalBeds' => 5, 'TelExtension' => '1']);
        StfRota::create(['StfRota_No' => 'R3', 'Stf_No' => 'S2003', 'Wd_No' => 'WD97', 'WkBegin' => '2026-08-19', 'Shift' => 'Night']);

        $this->delete('/staff/S2003')->assertRedirect('/staff');

        $this->assertDatabaseMissing('Stf', ['Stf_No' => 'S2003']);
        $this->assertDatabaseMissing('StfQual', ['Stf_No' => 'S2003']);
        $this->assertDatabaseMissing('StfWorkExp', ['Stf_No' => 'S2003']);
        $this->assertDatabaseMissing('StfRota', ['Stf_No' => 'S2003']);
    }

    public function test_search_finds_staff_by_qualification_and_work_experience(): void
    {
        $staff = Stf::create(['Stf_No' => 'S2004', 'FirstName' => 'Find', 'LastName' => 'Me']);
        StfQual::create(['Qual_No' => 'Q1', 'Stf_No' => 'S2004', 'Type' => 'RGN', 'Institution' => 'NMC']);
        StfWorkExp::create(['WorkExp_No' => 'E1', 'Stf_No' => 'S2004', 'Organization' => 'St Marys', 'Position' => 'Sister']);

        $this->get('/staff/search?qualification=RGN')->assertSee('Find Me');
        $this->get('/staff/search?organization=St%20Marys')->assertSee('Find Me');
        $this->get('/staff/search?qualification=PhD')->assertDontSee('Find Me');
    }

    public function test_ward_report_lists_staff_allocations(): void
    {
        Stf::create(['Stf_No' => 'S2005', 'FirstName' => 'Alloc', 'LastName' => 'ated']);
        Wd::create(['Wd_No' => 'WD99', 'Wd_Name' => 'Test Ward', 'Location' => 'Block X', 'TotalBeds' => 10, 'TelExtension' => '9999']);
        StfRota::create(['StfRota_No' => 'R1', 'Stf_No' => 'S2005', 'Wd_No' => 'WD99', 'WkBegin' => '2026-08-19', 'Shift' => 'Night']);

        $response = $this->get('/wards/report?date=2026-08-19');

        $response->assertOk()->assertSee('Test Ward')->assertSee('Alloc ated')->assertSee('Night');
    }

    public function test_allocation_can_be_recorded(): void
    {
        Stf::create(['Stf_No' => 'S2006', 'FirstName' => 'Shift', 'LastName' => 'Worker']);
        Wd::create(['Wd_No' => 'WD98', 'Wd_Name' => 'Another Ward', 'Location' => 'Block Y', 'TotalBeds' => 8, 'TelExtension' => '8888']);

        $this->post('/rota', [
            'Stf_No' => 'S2006',
            'Wd_No' => 'WD98',
            'WkBegin' => '2026-08-20',
            'Shift' => 'Morning',
        ])->assertRedirect('/rota');

        $this->assertDatabaseHas('StfRota', ['Stf_No' => 'S2006', 'Wd_No' => 'WD98', 'Shift' => 'Morning']);
    }

    public function test_conflicting_allocation_is_blocked(): void
    {
        Stf::create(['Stf_No' => 'S2007', 'FirstName' => 'Double', 'LastName' => 'Booked']);
        Wd::create(['Wd_No' => 'WD97', 'Wd_Name' => 'Ward A', 'TotalBeds' => 5]);
        Wd::create(['Wd_No' => 'WD96', 'Wd_Name' => 'Ward B', 'TotalBeds' => 5]);
        StfRota::create(['StfRota_No' => 'R1', 'Stf_No' => 'S2007', 'Wd_No' => 'WD97', 'WkBegin' => '2026-08-17', 'Shift' => 'Night']);

        $this->post('/rota', [
            'Stf_No' => 'S2007',
            'Wd_No' => 'WD96',
            'WkBegin' => '2026-08-17',
            'Shift' => 'Morning',
        ]);

        $this->assertDatabaseMissing('StfRota', ['Stf_No' => 'S2007', 'Wd_No' => 'WD96']);
    }

    public function test_rota_entry_can_be_updated(): void
    {
        Stf::create(['Stf_No' => 'S2008', 'FirstName' => 'Updat', 'LastName' => 'able']);
        Wd::create(['Wd_No' => 'WD95', 'Wd_Name' => 'Ward C', 'TotalBeds' => 5]);
        StfRota::create(['StfRota_No' => 'R1', 'Stf_No' => 'S2008', 'Wd_No' => 'WD95', 'WkBegin' => '2026-08-17', 'Shift' => 'Night']);

        $this->put('/rota/R1', [
            'Stf_No' => 'S2008',
            'Wd_No' => 'WD95',
            'WkBegin' => '2026-08-24',
            'Shift' => 'Evening',
        ])->assertRedirect('/rota');

        $this->assertDatabaseHas('StfRota', ['StfRota_No' => 'R1', 'Shift' => 'Evening']);
        $rota = StfRota::where('StfRota_No', 'R1')->first();
        $this->assertEquals('2026-08-24', $rota->WkBegin->format('Y-m-d'));
    }
}
