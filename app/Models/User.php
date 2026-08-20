<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable(['name', 'email', 'password', 'role'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    /**
     * Avatar initials derived from the name, falling back to the email.
     * Two words -> first letters; single word -> first character; empty name -> first email character.
     */
    public function getInitialsAttribute(): string
    {
        $source = trim((string) $this->name);

        if ($source === '') {
            $source = trim((string) $this->email);
        }

        $words = preg_split('/\s+/u', $source, -1, PREG_SPLIT_NO_EMPTY);

        if (empty($words)) {
            return '?';
        }

        $initials = mb_strtoupper(mb_substr($words[0], 0, 1));

        if (count($words) > 1) {
            $initials .= mb_strtoupper(mb_substr($words[1], 0, 1));
        }

        return $initials;
    }
}
