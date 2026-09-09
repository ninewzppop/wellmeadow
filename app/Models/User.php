<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable(['name', 'email', 'password', 'role', 'stf_no'])]
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

    public const ROLE_MEDICAL_DIRECTOR = 'medical_director';

    public const ROLE_PERSONNEL_OFFICER = 'personnel_officer';

    public const ROLE_CHARGE_NURSE = 'charge_nurse';

    public const ROLE_DOCTOR = 'doctor';

    public const ROLE_CONSULTANT = 'consultant';

    public const ROLE_SENIOR_NURSE = 'senior_nurse';

    public const ROLE_STAFF_NURSE = 'staff_nurse';

    public const ROLE_AUXILIARY = 'auxiliary';

    public const ROLES = [
        self::ROLE_MEDICAL_DIRECTOR,
        self::ROLE_PERSONNEL_OFFICER,
        self::ROLE_CHARGE_NURSE,
        self::ROLE_DOCTOR,
        self::ROLE_CONSULTANT,
        self::ROLE_SENIOR_NURSE,
        self::ROLE_STAFF_NURSE,
        self::ROLE_AUXILIARY,
    ];

    /** Roles allowed to open patient/medication areas at all. */
    public const CARE_ROLES = [
        self::ROLE_MEDICAL_DIRECTOR,
        self::ROLE_CHARGE_NURSE,
        self::ROLE_DOCTOR,
        self::ROLE_CONSULTANT,
        self::ROLE_SENIOR_NURSE,
        self::ROLE_STAFF_NURSE,
    ];

    public function hasRole(string|array $roles): bool
    {
        return in_array($this->role, (array) $roles, true);
    }

    public function isMedicalDirector(): bool
    {
        return $this->role === self::ROLE_MEDICAL_DIRECTOR;
    }

    public function isAdmin(): bool
    {
        // ADR-12.2: legacy 'admin' merged into medical_director.
        return $this->isMedicalDirector();
    }

    public function isClinician(): bool
    {
        return $this->hasRole([self::ROLE_DOCTOR, self::ROLE_CONSULTANT]);
    }

    /** Salary group is hidden from doctors/consultants (ADR-12.6). */
    public function canViewSalary(): bool
    {
        return ! $this->isClinician();
    }

    /**
     * The staff record this login account belongs to (nullable for legacy accounts).
     */
    public function staff(): BelongsTo
    {
        return $this->belongsTo(Stf::class, 'stf_no', 'Stf_No');
    }

    /** Own ward from the linked staff record (null = unlinked or unallocated). */
    public function wardNo(): ?string
    {
        $ward = $this->staff?->Alloc_Wd_No;

        return $ward === null ? null : (string) $ward;
    }

    /**
     * Whether ward filters are bypassed: medical directors always,
     * plus staff without an allocated ward (ADR-12.5).
     */
    public function managesAllWards(): bool
    {
        return $this->isMedicalDirector() || $this->wardNo() === null;
    }

    /**
     * Patient IDs visible to this user. Null = unrestricted (all patients).
     * Doctors: via Appointment.Consult_Stf_No (ADR-12.4).
     * Ward roles: via InPatient beds in own ward.
     */
    public function accessiblePatientIds(): ?array
    {
        if ($this->isMedicalDirector()) {
            return null;
        }

        if ($this->isClinician()) {
            $stfNo = $this->stf_no;

            if ($stfNo === null) {
                return [];
            }

            return Appointment::where('Consult_Stf_No', $stfNo)->distinct()->pluck('Pt_No')->all();
        }

        if ($this->hasRole([self::ROLE_CHARGE_NURSE, self::ROLE_SENIOR_NURSE, self::ROLE_STAFF_NURSE])) {
            if ($this->managesAllWards()) {
                return null;
            }

            return InPatient::whereHas('bed', fn ($q) => $q->where('Wd_No', $this->wardNo()))
                ->distinct()->pluck('Pt_No')->all();
        }

        return [];
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
