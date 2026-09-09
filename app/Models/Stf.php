<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Stf extends Model
{
    use HasFactory;

    protected $table = 'Stf';

    protected $primaryKey = 'Stf_No';

    public $incrementing = false;

    protected $keyType = 'string';

    public $timestamps = false;

    protected $fillable = [
        'Stf_No', 'FirstName', 'LastName', 'Address', 'TelNo', 'DOB', 'Sex', 'NIN', 'Alloc_Wd_No',
    ];

    protected $casts = [
        'DOB' => 'date',
    ];

    public function getFullNameAttribute(): string
    {
        return trim($this->FirstName.' '.$this->LastName);
    }

    public function qualifications(): HasMany
    {
        return $this->hasMany(StfQual::class, 'Stf_No', 'Stf_No');
    }

    public function positions(): HasMany
    {
        return $this->hasMany(StfPos::class, 'Stf_No', 'Stf_No');
    }

    public function workExperiences(): HasMany
    {
        return $this->hasMany(StfWorkExp::class, 'Stf_No', 'Stf_No');
    }

    public function assignedWard(): BelongsTo
    {
        return $this->belongsTo(Wd::class, 'Alloc_Wd_No', 'Wd_No');
    }

    public function rotas(): HasMany
    {
        return $this->hasMany(StfRota::class, 'Stf_No', 'Stf_No');
    }

    public function appointments(): HasMany
    {
        return $this->hasMany(Appointment::class, 'Consult_Stf_No', 'Stf_No');
    }

    /** Login account linked to this staff member, if any. */
    public function user(): HasOne
    {
        return $this->hasOne(User::class, 'stf_no', 'Stf_No');
    }

    public function role(): ?string
    {
        return $this->user?->role;
    }

    public function hasRole(string|array $role): bool
    {
        return in_array($this->role(), (array) $role, true);
    }

    /**
     * Next auto-generated staff number (S1001, S1002, …).
     * Continues from the highest numeric S-suffix; starts at S1001 on an empty table.
     */
    public static function nextNo(): string
    {
        $max = static::query()
            ->where('Stf_No', 'like', 'S%')
            ->pluck('Stf_No')
            ->map(fn (string $stfNo) => (int) substr($stfNo, 1))
            ->max();

        return 'S'.max(1001, ($max ?? 0) + 1);
    }

    /**
     * Ward membership check. Medical directors and staff without an
     * allocated ward match every ward (ADR-12.5).
     */
    public function isInWard(string|int $wardNo): bool
    {
        if ($this->hasRole(User::ROLE_MEDICAL_DIRECTOR) || $this->Alloc_Wd_No === null) {
            return true;
        }

        $mine = (string) $this->Alloc_Wd_No;
        $want = (string) $wardNo;

        if ($mine === $want) {
            return true;
        }

        // Tolerate numeric input ("1" matches "WD01").
        return (int) filter_var($mine, FILTER_SANITIZE_NUMBER_INT) === (int) $want
            && (int) $want !== 0;
    }

    public function canManagePatient(): bool
    {
        return $this->hasRole([
            User::ROLE_MEDICAL_DIRECTOR,
            User::ROLE_CHARGE_NURSE,
            User::ROLE_DOCTOR,
            User::ROLE_CONSULTANT,
        ]);
    }

    public function canManageStaff(): bool
    {
        return $this->hasRole([
            User::ROLE_MEDICAL_DIRECTOR,
            User::ROLE_PERSONNEL_OFFICER,
        ]);
    }

    public function canManageSupply(): bool
    {
        return $this->hasRole([
            User::ROLE_MEDICAL_DIRECTOR,
            User::ROLE_CHARGE_NURSE,
        ]);
    }
}
