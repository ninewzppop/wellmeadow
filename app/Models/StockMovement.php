<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StockMovement extends Model
{
    protected $table = 'StockMovement';

    public $timestamps = false;

    protected $fillable = [
        'Drug_No', 'Item_No', 'QtyChange', 'Note', 'Moved_By', 'MoveDate',
    ];

    protected $casts = [
        'MoveDate' => 'datetime',
        'QtyChange' => 'integer',
    ];

    public function drug(): BelongsTo
    {
        return $this->belongsTo(Pharmaceutical::class, 'Drug_No', 'Drug_No');
    }

    public function item(): BelongsTo
    {
        return $this->belongsTo(CentralStock::class, 'Item_No', 'Item_No');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'Moved_By');
    }

    public function directionLabel(): string
    {
        return $this->QtyChange > 0 ? __('Restock') : __('Adjustment');
    }
}
