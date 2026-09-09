<?php

namespace Tests\Feature;

use App\Models\Appointment;
use App\Models\Bed;
use App\Models\InPatient;
use App\Models\Patient;
use App\Models\Room;
use App\Models\Stf;
use App\Models\User;
use App\Models\Wd;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Tests\TestCase;

class RoleAccessTest extends TestCase
{
    use RefreshDatabase;

    private User $director;

    private User $personnel;

    private User $charge;

    private User $doctor;

    private User $nurse;

    private User $aux;

    protected function setUp(): void
    {
        parent::setUp();

        Wd::create(['Wd_No' => 'WD01', 'Wd_Name' => 'Cardiology', 'Location' => 'A1', 'TotalBeds' => 14, 'TelExtension' => '2101']);
        Wd::create(['Wd_No' => 'WD02', 'Wd_Name' => 'Paediatrics', 'Location' => 'A2', 'TotalBeds' => 14, 'TelExtension' => '2102']);
        Bed::create(['Bed_No' => '101', 'Wd_No' => 'WD01', 'BedStatus' => 'Occupied']);
        Bed::create(['Bed_No' => '201', 'Wd_No' => 'WD02', 'BedStatus' => 'Occupied']);
        Room::create(['Room_No' => 'R001', 'RoomName' => 'Room 1', 'Location' => 'A']);

        $chargeStf = Stf::create(['Stf_No' => 'S9001', 'FirstName' => 'Charge', 'LastName' => 'One', 'Alloc_Wd_No' => 'WD01']);
        $doctorStf = Stf::create(['Stf_No' => 'S9002', 'FirstName' => 'Doc', 'LastName' => 'Two', 'Alloc_Wd_No' => 'WD01']);
        $otherStf = Stf::create(['Stf_No' => 'S9003', 'FirstName' => 'Other', 'LastName' => 'Doc', 'Alloc_Wd_No' => 'WD02']);
        Stf::create(['Stf_No' => 'S9004', 'FirstName' => 'Nurse', 'LastName' => 'Four', 'Alloc_Wd_No' => 'WD01']);
        Stf::create(['Stf_No' => 'S9005', 'FirstName' => 'Aux', 'LastName' => 'Five', 'Alloc_Wd_No' => 'WD01']);

        Patient::create(['Pt_No' => 'PT901', 'FirstName' => 'Ann', 'LastName' => 'A']);
        Patient::create(['Pt_No' => 'PT902', 'FirstName' => 'Bob', 'LastName' => 'B']);
        InPatient::create(['In_Pt_No' => 'IP901', 'Pt_No' => 'PT901', 'Bed_No' => '101']);
        InPatient::create(['In_Pt_No' => 'IP902', 'Pt_No' => 'PT902', 'Bed_No' => '201']);
        Appointment::create(['Appt_No' => 'A901', 'Pt_No' => 'PT901', 'Consult_Stf_No' => 'S9002', 'Room_No' => 'R001', 'ApptDate' => today(), 'ApptTime' => '09:00', 'status' => 'scheduled']);
        Appointment::create(['Appt_No' => 'A902', 'Pt_No' => 'PT902', 'Consult_Stf_No' => 'S9003', 'Room_No' => 'R001', 'ApptDate' => today(), 'ApptTime' => '10:00', 'status' => 'scheduled']);

        $this->director = $this->makeUser('director@t.test', 'medical_director', null);
        $this->personnel = $this->makeUser('personnel@t.test', 'personnel_officer', null);
        $this->charge = $this->makeUser('charge@t.test', 'charge_nurse', 'S9001');
        $this->doctor = $this->makeUser('doctor@t.test', 'doctor', 'S9002');
        $this->nurse = $this->makeUser('nurse@t.test', 'staff_nurse', 'S9004');
        $this->aux = $this->makeUser('aux@t.test', 'auxiliary', 'S9005');
    }

    private function makeUser(string $email, string $role, ?string $stfNo): User
    {
        return User::create(['name' => $role, 'email' => $email, 'password' => 'password', 'role' => $role, 'stf_no' => $stfNo]);
    }

    public function test_director_opens_everything(): void
    {
        $this->actingAs($this->director)->get('/staff')->assertOk();
        $this->actingAs($this->director)->get('/patients')->assertOk();
        $this->actingAs($this->director)->get('/requisitions')->assertOk();
    }

    public function test_personnel_manages_staff_but_never_patients(): void
    {
        $this->actingAs($this->personnel)->get('/staff')->assertOk();
        $this->actingAs($this->personnel)->get('/patients')->assertRedirect('/forbidden');
        $this->actingAs($this->personnel)->get('/medications')->assertRedirect('/forbidden');
        $this->actingAs($this->personnel)->get('/rota')->assertRedirect('/forbidden');
    }

    public function test_auxiliary_sees_only_own_rota(): void
    {
        $this->actingAs($this->aux)->get('/rota')->assertOk();
        $this->actingAs($this->aux)->get('/patients')->assertRedirect('/forbidden');
        $this->actingAs($this->aux)->get('/staff')->assertRedirect('/forbidden');
        $this->actingAs($this->aux)->get('/requisitions')->assertRedirect('/forbidden');
    }

    public function test_nurse_cannot_open_staff_pages(): void
    {
        $this->actingAs($this->nurse)->get('/staff')->assertRedirect('/forbidden');
        $this->actingAs($this->nurse)->get('/patients')->assertOk();
    }

    public function test_doctor_lists_only_own_appointments(): void
    {
        $response = $this->actingAs($this->doctor)->get('/appointments');

        $response->assertOk();
        $response->assertSee('A901');
        $response->assertDontSee('A902');
    }

    public function test_doctor_cannot_open_other_doctor_patient(): void
    {
        $this->actingAs($this->doctor)->get('/patients/PT901')->assertOk();
        $this->actingAs($this->doctor)->get('/patients/PT902')->assertRedirect('/forbidden');
    }

    public function test_charge_lists_only_own_ward_inpatients(): void
    {
        $response = $this->actingAs($this->charge)->get('/in-patients');

        $response->assertOk();
        $response->assertSee('IP901');
        $response->assertDontSee('IP902');
    }

    public function test_forbidden_page_has_role_back_link(): void
    {
        $this->actingAs($this->aux)->get('/forbidden')
            ->assertStatus(403)
            ->assertSee(route('rota.index'), false);
    }

    public function test_password_change_flow(): void
    {
        $this->actingAs($this->nurse)->get('/profile/password')->assertOk();

        $this->actingAs($this->nurse)->put('/profile/password', [
            'current_password' => 'password',
            'password' => 'new-password-1',
            'password_confirmation' => 'new-password-1',
        ])->assertSessionHasNoErrors();

        $this->assertTrue(Hash::check('new-password-1', $this->nurse->fresh()->password));

        $this->actingAs($this->nurse)->put('/profile/password', [
            'current_password' => 'wrong',
            'password' => 'another-pass-1',
            'password_confirmation' => 'another-pass-1',
        ])->assertSessionHasErrors('current_password');
    }

    public function test_login_writes_audit_log(): void
    {
        Log::spy();

        $this->post('/login', ['email' => 'nurse@t.test', 'password' => 'password'])->assertRedirect('/');

        Log::shouldHaveReceived('info')->with('auth.login', \Mockery::on(
            fn ($ctx) => ($ctx['email'] ?? null) === 'nurse@t.test' && isset($ctx['user_id'], $ctx['ip'])
        ));
    }

    public function test_staff_helpers(): void
    {
        $chargeStf = Stf::find('S9001');

        $this->assertTrue($chargeStf->hasRole('charge_nurse'));
        $this->assertTrue($chargeStf->isInWard('WD01'));
        $this->assertFalse($chargeStf->isInWard('WD02'));
        $this->assertTrue($chargeStf->canManagePatient());
        $this->assertFalse($chargeStf->canManageStaff());
        $this->assertTrue($chargeStf->canManageSupply());
    }
}
