<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Patient extends Model
{
    protected $table = 'Patient';

    protected $primaryKey = 'Pt_No';

    public $incrementing = false;

    protected $keyType = 'string';

    public $timestamps = false;

    protected $fillable = [
        'Pt_No', 'FirstName', 'LastName', 'Address', 'TelNo', 'DOB', 'Sex', 'MaritalStat', 'DateReg', 'Clinic_No',
    ];

    protected $casts = [
        'DOB' => 'date',
        'DateReg' => 'date',
    ];

    public function getFullNameAttribute(): string
    {
        return trim($this->FirstName.' '.$this->LastName);
    }

    public function localDoctor(): BelongsTo
    {
        return $this->belongsTo(LocalDr::class, 'Clinic_No', 'Clinic_No');
    }

    public function appointments(): HasMany
    {
        return $this->hasMany(Appointment::class, 'Pt_No', 'Pt_No');
    }

    public function inPatients(): HasMany
    {
        return $this->hasMany(InPatient::class, 'Pt_No', 'Pt_No');
    }

    public function medications(): HasMany
    {
        return $this->hasMany(Medications::class, 'Pt_No', 'Pt_No');
    }

    public function nextOfKins(): HasMany
    {
        return $this->hasMany(NextOfKin::class, 'Pt_No', 'Pt_No');
    }

    public function allergies(): HasMany
    {
        return $this->hasMany(PatientAllergy::class, 'Pt_No', 'Pt_No');
    }
}
