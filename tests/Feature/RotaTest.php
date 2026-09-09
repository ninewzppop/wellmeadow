<?php

namespace Tests\Feature;

use App\Models\Stf;
use App\Models\StfRota;
use App\Models\User;
use App\Models\Wd;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RotaTest extends TestCase
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

    public function test_rota_page_loads(): void
    {
        $this->get('/rota')
            ->assertOk()
            ->assertSee('Staff Rota');
    }

    public function test_rota_lists_entries_and_flags_cross_ward_conflicts(): void
    {
        Wd::create(['Wd_No' => 'WD01', 'Wd_Name' => 'Cardiology', 'TotalBeds' => 2]);
        Wd::create(['Wd_No' => 'WD02', 'Wd_Name' => 'Surgery', 'TotalBeds' => 3]);
        Stf::create(['Stf_No' => 'S1001', 'FirstName' => 'Anna', 'LastName' => 'Smith']);
        Stf::create(['Stf_No' => 'S1002', 'FirstName' => 'Ben', 'LastName' => 'Jones']);

        $week = today()->startOfWeek();

        StfRota::create(['StfRota_No' => 'R1', 'Stf_No' => 'S1001', 'Wd_No' => 'WD01', 'WkBegin' => $week, 'Shift' => 'Morning']);
        StfRota::create(['StfRota_No' => 'R2', 'Stf_No' => 'S1001', 'Wd_No' => 'WD02', 'WkBegin' => $week, 'Shift' => 'Night']);
        StfRota::create(['StfRota_No' => 'R3', 'Stf_No' => 'S1002', 'Wd_No' => 'WD01', 'WkBegin' => $week->copy()->addWeek(), 'Shift' => 'Evening']);

        $this->get('/rota')
            ->assertOk()
            ->assertSee('Anna Smith')
            ->assertSee('Conflict');

        $this->get('/rota?date='.$week->toDateString())
            ->assertOk()
            ->assertSee('Anna Smith')
            ->assertSee($week->format('d M Y'))
            ->assertDontSee($week->copy()->addWeek()->format('d M Y'));
    }

    public function test_empty_rota_shows_placeholder_message(): void
    {
        $this->get('/rota')
            ->assertOk()
            ->assertSee('No roster entries yet.');
    }
}
