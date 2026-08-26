<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Itemrequest extends Model
{
    protected $table = 'Itemrequest';

    public $incrementing = false;

    protected $primaryKey = null;

    public $timestamps = false;

    protected $fillable = [
        'Wd_Req_No', 'Item_No', 'QtyReq',
    ];

    protected $casts = [
        'QtyReq' => 'integer',
    ];

    public function requisition(): BelongsTo
    {
        return $this->belongsTo(Wardrequisition::class, 'Wd_Req_No', 'Wd_Req_No');
    }

    public function item(): BelongsTo
    {
        return $this->belongsTo(CentralStock::class, 'Item_No', 'Item_No');
    }
}
