<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Outpatient extends Model
{
    protected $table = 'Outpatient';

    protected $primaryKey = 'Appt_out_No';

    public $incrementing = false;

    protected $keyType = 'string';

    public $timestamps = false;

    public function appointment(): BelongsTo
    {
        return $this->belongsTo(Appointment::class, 'Appt_out_No', 'Appt_No');
    }
}
