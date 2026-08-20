<?php

namespace Tests\Unit;

use App\Models\User;
use PHPUnit\Framework\TestCase;

class UserInitialsTest extends TestCase
{
    public function test_initials_from_two_word_name(): void
    {
        $user = new User(['name' => 'Test Admin']);

        $this->assertSame('TA', $user->initials);
    }

    public function test_initials_from_single_word_name(): void
    {
        $user = new User(['name' => 'Laravel']);

        $this->assertSame('L', $user->initials);
    }

    public function test_initials_from_thai_single_word_name(): void
    {
        $user = new User(['name' => 'ณัชชา']);

        $this->assertSame('ณ', $user->initials);
    }

    public function test_initials_fall_back_to_email_when_name_is_empty(): void
    {
        $user = new User(['name' => '', 'email' => 'john@example.com']);

        $this->assertSame('J', $user->initials);
    }

    public function test_initials_fallback_when_name_and_email_are_empty(): void
    {
        $user = new User(['name' => null, 'email' => null]);

        $this->assertSame('?', $user->initials);
    }
}
