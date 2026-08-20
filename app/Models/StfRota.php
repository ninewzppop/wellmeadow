<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StfRota extends Model
{
    protected $table = 'StfRota';

    protected $primaryKey = 'StfRota_No';

    public $incrementing = false;

    protected $keyType = 'string';

    public $timestamps = false;

    protected $fillable = [
        'StfRota_No', 'Stf_No', 'Wd_No', 'WkBegin', 'Shift',
    ];

    protected $casts = [
        'WkBegin' => 'date',
    ];

    public function stf(): BelongsTo
    {
        return $this->belongsTo(Stf::class, 'Stf_No', 'Stf_No');
    }

    public function wd(): BelongsTo
    {
        return $this->belongsTo(Wd::class, 'Wd_No', 'Wd_No');
    }
}
