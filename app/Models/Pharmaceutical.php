<?php

namespace App\Models;

use App\Models\Concerns\HasExpiryStatus;
use App\Models\Concerns\HasStockStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Pharmaceutical extends Model
{
    use HasExpiryStatus;
    use HasStockStatus;

    protected $table = 'Pharmaceutical';

    protected $primaryKey = 'Drug_No';

    public $incrementing = false;

    protected $keyType = 'string';

    public $timestamps = false;

    protected $fillable = [
        'Drug_No', 'Name', 'Description', 'Dosage', 'AdminMethod', 'QtyInStock', 'ReorderLvl', 'CostPerUnit', 'Suppl_No', 'ExpiryDate',
    ];

    protected $casts = [
        'ExpiryDate' => 'date:Y-m-d',
    ];

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class, 'Suppl_No', 'Suppl_No');
    }

    public function movements(): HasMany
    {
        return $this->hasMany(StockMovement::class, 'Drug_No', 'Drug_No');
    }
}
