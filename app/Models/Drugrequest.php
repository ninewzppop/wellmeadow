<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Drugrequest extends Model
{
    protected $table = 'Drugrequest';

    public $incrementing = false;

    protected $primaryKey = null;

    public $timestamps = false;

    protected $fillable = [
        'Wd_Req_No', 'Drug_No', 'QtyReq',
    ];

    protected $casts = [
        'QtyReq' => 'integer',
    ];

    public function requisition(): BelongsTo
    {
        return $this->belongsTo(Wardrequisition::class, 'Wd_Req_No', 'Wd_Req_No');
    }

    public function drug(): BelongsTo
    {
        return $this->belongsTo(Pharmaceutical::class, 'Drug_No', 'Drug_No');
    }
}
