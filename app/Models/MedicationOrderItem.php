<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MedicationOrderItem extends Model
{
    protected $table = 'MedicationOrderItem';

    public $timestamps = false;

    protected $fillable = [
        'Order_No', 'Drug_No', 'UnitsPerDay', 'AdminMethod', 'StartDate', 'FinishDate',
    ];

    protected $casts = [
        'StartDate' => 'date',
        'FinishDate' => 'date',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(MedicationOrder::class, 'Order_No', 'Order_No');
    }

    public function drug(): BelongsTo
    {
        return $this->belongsTo(Pharmaceutical::class, 'Drug_No', 'Drug_No');
    }
}
