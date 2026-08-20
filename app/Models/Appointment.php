<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Appointment extends Model
{
    protected $table = 'Appointment';

    protected $primaryKey = 'Appt_No';

    public $incrementing = false;

    protected $keyType = 'string';

    public $timestamps = false;

    protected $fillable = [
        'Appt_No', 'Pt_No', 'Consult_Stf_No', 'ApptDate', 'ApptTime', 'Room_No', 'status',
    ];

    protected $casts = [
        'ApptDate' => 'date',
        'ApptTime' => 'datetime:H:i',
    ];

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class, 'Pt_No', 'Pt_No');
    }

    public function consultant(): BelongsTo
    {
        return $this->belongsTo(Stf::class, 'Consult_Stf_No', 'Stf_No');
    }

    public function room(): BelongsTo
    {
        return $this->belongsTo(Room::class, 'Room_No', 'Room_No');
    }

    public function outpatient(): HasOne
    {
        return $this->hasOne(Outpatient::class, 'Appt_out_No', 'Appt_No');
    }
}
