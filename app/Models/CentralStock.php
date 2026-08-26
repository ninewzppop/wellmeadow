<?php

namespace App\Models;

use App\Models\Concerns\HasStockStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CentralStock extends Model
{
    use HasStockStatus;

    public const TYPE_SURGICAL = 'surgical';

    public const TYPE_NON_SURGICAL = 'non-surgical';

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

    public function movements(): HasMany
    {
        return $this->hasMany(StockMovement::class, 'Item_No', 'Item_No');
    }

    public static function itemTypes(): array
    {
        return [
            self::TYPE_SURGICAL => __('surgical'),
            self::TYPE_NON_SURGICAL => __('non-surgical'),
        ];
    }
}
