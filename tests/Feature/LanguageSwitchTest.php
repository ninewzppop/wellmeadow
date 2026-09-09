<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LanguageSwitchTest extends TestCase
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

    public function test_default_locale_is_english(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertSee('Dashboard')
            ->assertSee('Total patients')
            ->assertSee('Patients');
    }

    public function test_switching_to_thai_translates_the_dashboard(): void
    {
        $this->get('/language/th')
            ->assertRedirect();

        $this->get('/')
            ->assertOk()
            ->assertSee('แดชบอร์ด')
            ->assertSee('ผู้ป่วยทั้งหมด')
            ->assertSee('ผู้ป่วย');
    }

    public function test_switching_back_to_english(): void
    {
        $this->get('/language/th');
        $this->get('/language/en');

        $this->get('/')->assertSee('Total patients');
    }

    public function test_invalid_locale_returns_404(): void
    {
        $this->get('/language/fr')->assertNotFound();
    }

    public function test_switcher_is_visible_on_login_page_for_guests(): void
    {
        auth()->logout();

        $this->get('/login')
            ->assertOk()
            ->assertSee('EN')
            ->assertSee('TH');
    }

    public function test_switcher_is_visible_in_header_for_authenticated_users(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertSee('EN')
            ->assertSee('TH');
    }
}
