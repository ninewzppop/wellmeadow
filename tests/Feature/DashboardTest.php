<?php

namespace Tests\Feature;

use App\Models\Appointment;
use App\Models\Bed;
use App\Models\Patient;
use App\Models\Stf;
use App\Models\StfRota;
use App\Models\User;
use App\Models\Wd;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardTest extends TestCase
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

    public function test_dashboard_is_the_home_page_and_loads(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertSee('Dashboard')
            ->assertSee('ผู้ป่วยทั้งหมด');
    }

    public function test_dashboard_shows_real_counts(): void
    {
        Wd::create(['Wd_No' => 'WD01', 'Wd_Name' => 'Cardiology', 'TotalBeds' => 2]);
        Bed::create(['Bed_No' => 'B01', 'Wd_No' => 'WD01', 'BedStatus' => 'Occupied']);
        Bed::create(['Bed_No' => 'B02', 'Wd_No' => 'WD01', 'BedStatus' => 'Available']);
        Patient::create(['Pt_No' => 'PT001', 'FirstName' => 'Test', 'LastName' => 'Patient', 'DateReg' => today()]);
        Stf::create(['Stf_No' => 'S1001', 'FirstName' => 'On', 'LastName' => 'Duty']);
        StfRota::create(['StfRota_No' => 'R1', 'Stf_No' => 'S1001', 'Wd_No' => 'WD01', 'WkBegin' => today()->startOfWeek(), 'Shift' => 'Morning']);
        Appointment::create([
            'Appt_No' => 'A001', 'Pt_No' => 'PT001', 'ApptDate' => today(),
            'ApptTime' => '09:30:00', 'status' => 'waiting list',
        ]);

        $response = $this->get('/');

        $response->assertOk()
            ->assertSee('50%') // 1 occupied / 2 beds
            ->assertSee('1')    // patients today
            ->assertSee('1');   // appointment today
    }

    public function test_guest_is_redirected_to_login(): void
    {
        auth()->logout();

        $this->get('/')->assertRedirect('/login');
    }

    public function test_admin_only_users_page_is_forbidden_for_staff(): void
    {
        $this->get('/users')->assertForbidden();
    }

    public function test_admin_can_access_users_page(): void
    {
        auth()->login(User::create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'password' => 'password123',
            'role' => 'admin',
        ]));

        $this->get('/users')->assertOk();
    }

    public function test_placeholder_pages_resolve(): void
    {
        foreach (['patients', 'appointments', 'wards', 'stock', 'pharmacy', 'suppliers'] as $page) {
            $this->get("/{$page}")->assertOk();
        }
    }
}
