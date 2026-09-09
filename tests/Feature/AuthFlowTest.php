<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthFlowTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        User::create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password123',
            'role' => 'medical_director',
        ]);
    }

    public function test_unauthenticated_user_is_redirected_to_login(): void
    {
        $this->get('/staff')->assertRedirect('/login');
        $this->get('/allocations')->assertRedirect('/login');
        $this->get('/wards/report')->assertRedirect('/login');
        $this->get('/')->assertRedirect('/login');
    }

    public function test_guest_sees_login_page(): void
    {
        $this->get('/login')->assertOk()->assertSee('Sign in');
    }

    public function test_authenticated_user_visiting_login_is_redirected(): void
    {
        $user = User::where('email', 'test@example.com')->first();

        $this->actingAs($user)->get('/login')->assertRedirect('/');
    }

    public function test_login_success_redirects_to_dashboard(): void
    {
        $this->post('/login', [
            'email' => 'test@example.com',
            'password' => 'password123',
        ])->assertRedirect('/');

        $this->assertAuthenticated();
    }

    public function test_login_failure_shows_error(): void
    {
        $response = $this->from('/login')->post('/login', [
            'email' => 'test@example.com',
            'password' => 'wrong-password',
        ]);

        $response->assertRedirect('/login');
        $response->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    public function test_logout_redirects_to_login_and_guest(): void
    {
        $user = User::where('email', 'test@example.com')->first();

        $this->actingAs($user)
            ->post('/logout')
            ->assertRedirect('/login');

        $this->assertGuest();
    }

    public function test_authenticated_user_can_access_staff(): void
    {
        $user = User::where('email', 'test@example.com')->first();

        $this->actingAs($user)->get('/staff')->assertOk();
    }
}
