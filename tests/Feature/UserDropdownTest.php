<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserDropdownTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->actingAs(User::create([
            'name' => 'Test Admin',
            'email' => 'admin@example.com',
            'password' => 'password123',
            'role' => 'admin',
        ]));
    }

    public function test_header_shows_user_dropdown_with_initials(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertSee('TA')          // initials avatar
            ->assertSee('Test Admin')  // name on trigger and in menu header
            ->assertSee('admin@example.com')
            ->assertSee('Admin');      // role badge
    }

    public function test_logout_form_uses_post_with_csrf(): void
    {
        $html = $this->get('/')->getContent();

        $this->assertStringContainsString('method="POST"', $html);
        $this->assertStringContainsString('name="_token"', $html);
        $this->assertStringContainsString('Logout', $html);
    }

    public function test_logout_logs_user_out(): void
    {
        $this->post('/logout')
            ->assertRedirect('/login');

        $this->assertGuest();
    }
}
