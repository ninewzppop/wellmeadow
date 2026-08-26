<?php

namespace App\Models\Concerns;

use Illuminate\Database\Eloquent\Builder;

trait HasStockStatus
{
    public const STOCK_OUT = 'out';

    public const STOCK_LOW = 'low';

    public const STOCK_NORMAL = 'normal';

    public static function stockStatusLabels(): array
    {
        return [
            self::STOCK_NORMAL => __('Normal'),
            self::STOCK_LOW => __('Low stock'),
            self::STOCK_OUT => __('Out of stock'),
        ];
    }

    public function getStockStatusLabelAttribute(): string
    {
        return static::stockStatusLabels()[$this->stock_status] ?? (string) $this->stock_status;
    }

    public function getStockStatusAttribute(): string
    {
        $qty = (int) ($this->QtyInStock ?? 0);

        if ($qty === 0) {
            return self::STOCK_OUT;
        }

        if ($this->ReorderLvl !== null && $qty <= $this->ReorderLvl) {
            return self::STOCK_LOW;
        }

        return self::STOCK_NORMAL;
    }

    public function scopeStockStatus(Builder $query, string $status): Builder
    {
        return match ($status) {
            self::STOCK_OUT => $query->whereRaw('COALESCE(QtyInStock, 0) = 0'),
            self::STOCK_LOW => $query
                ->whereRaw('COALESCE(QtyInStock, 0) > 0')
                ->whereNotNull('ReorderLvl')
                ->whereRaw('COALESCE(QtyInStock, 0) <= ReorderLvl'),
            self::STOCK_NORMAL => $query
                ->whereRaw('COALESCE(QtyInStock, 0) > 0')
                ->where(function (Builder $q) {
                    $q->whereNull('ReorderLvl')->whereRaw('COALESCE(QtyInStock, 0) > 0')
                        ->orWhereRaw('COALESCE(QtyInStock, 0) > ReorderLvl');
                }),
            default => $query,
        };
    }

    public function scopeNeedsRestock(Builder $query): Builder
    {
        return $query->where(function (Builder $q) {
            $q->whereRaw('COALESCE(QtyInStock, 0) = 0')
                ->orWhere(function (Builder $qq) {
                    $qq->whereRaw('COALESCE(QtyInStock, 0) > 0')
                        ->whereNotNull('ReorderLvl')
                        ->whereRaw('COALESCE(QtyInStock, 0) <= ReorderLvl');
                });
        })
            ->orderByRaw('CASE WHEN COALESCE(QtyInStock, 0) = 0 THEN 0 ELSE 1 END')
            ->orderBy('Name');
    }

    public static function stockCounts(): array
    {
        $base = static::query();

        return [
            'total' => (clone $base)->count(),
            'out' => (clone $base)->stockStatus(self::STOCK_OUT)->count(),
            'low' => (clone $base)->stockStatus(self::STOCK_LOW)->count(),
            'normal' => (clone $base)->stockStatus(self::STOCK_NORMAL)->count(),
        ];
    }
}
