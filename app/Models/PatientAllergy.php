<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PatientAllergy extends Model
{
    protected $table = 'PatientAllergy';

    protected $primaryKey = 'Allergy_No';

    public $incrementing = false;

    protected $keyType = 'string';

    public $timestamps = false;

    protected $fillable = [
        'Allergy_No', 'Pt_No', 'Drug_No', 'Allergy_Name', 'Reaction', 'Severity', 'DiagDate', 'Rec_Stf_No',
    ];

    protected $casts = [
        'DiagDate' => 'date',
    ];

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class, 'Pt_No', 'Pt_No');
    }

    public function drug(): BelongsTo
    {
        return $this->belongsTo(Pharmaceutical::class, 'Drug_No', 'Drug_No');
    }

    public function recordedBy(): BelongsTo
    {
        return $this->belongsTo(Stf::class, 'Rec_Stf_No', 'Stf_No');
    }
}
