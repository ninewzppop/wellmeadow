<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Bed extends Model
{
    protected $table = 'Bed';

    protected $primaryKey = 'Bed_No';

    public $incrementing = false;

    protected $keyType = 'string';

    public $timestamps = false;

    protected $fillable = [
        'Bed_No', 'Wd_No', 'BedStatus',
    ];

    public function ward(): BelongsTo
    {
        return $this->belongsTo(Wd::class, 'Wd_No', 'Wd_No');
    }
}