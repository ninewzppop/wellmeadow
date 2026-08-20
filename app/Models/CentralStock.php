<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CentralStock extends Model
{
    protected $table = 'CentralStock';

    protected $primaryKey = 'Item_No';

    public $incrementing = false;

    protected $keyType = 'string';

    public $timestamps = false;

    protected $fillable = [
        'Item_No', 'Name', 'ItemType', 'Description', 'QtyInStock', 'ReorderLvl', 'CostPerUnit', 'Suppl_No',
    ];

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class, 'Suppl_No', 'Suppl_No');
    }
}
