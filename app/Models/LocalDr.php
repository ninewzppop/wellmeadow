<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LocalDr extends Model
{
    protected $table = 'LocalDr';

    protected $primaryKey = 'Clinic_No';

    public $incrementing = false;

    protected $keyType = 'string';

    public $timestamps = false;

    public function getRouteKeyName(): string
    {
        return 'Clinic_No';
    }

    protected $fillable = [
        'Clinic_No', 'FirstName', 'LastName', 'Address', 'TelNo',
    ];

    public function getFullNameAttribute(): string
    {
        return trim($this->FirstName.' '.$this->LastName);
    }

    /**
     * Next auto-generated clinic number (LD01, LD02, …).
     */
    public static function nextNo(): string
    {
        $max = static::query()
            ->where('Clinic_No', 'like', 'LD%')
            ->pluck('Clinic_No')
            ->map(fn (string $clinicNo) => (int) substr($clinicNo, 2))
            ->max();

        return 'LD'.str_pad((string) (($max ?? 0) + 1), 2, '0', STR_PAD_LEFT);
    }

    public function patients(): HasMany
    {
        return $this->hasMany(Patient::class, 'Clinic_No', 'Clinic_No');
    }
}
