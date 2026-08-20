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

    protected $fillable = [
        'Clinic_No', 'FirstName', 'LastName', 'Address', 'TelNo',
    ];

    public function getFullNameAttribute(): string
    {
        return trim($this->FirstName.' '.$this->LastName);
    }

    public function patients(): HasMany
    {
        return $this->hasMany(Patient::class, 'Clinic_No', 'Clinic_No');
    }
}
