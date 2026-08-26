<?php

namespace App\Models\Concerns;

use Illuminate\Database\Eloquent\Builder;

trait HasExpiryStatus
{
    public const EXPIRY_OK = 'ok';

    public const EXPIRY_NEAR = 'near-expiry';

    public const EXPIRY_EXPIRED = 'expired';

    public const NEAR_EXPIRY_DAYS = 90;

    public static function expiryStatusLabels(): array
    {
        return [
            self::EXPIRY_OK => __('Expiry OK'),
            self::EXPIRY_NEAR => __('Near expiry'),
            self::EXPIRY_EXPIRED => __('Expired'),
        ];
    }

    public function getExpiryStatusLabelAttribute(): string
    {
        if ($this->expiry_status === null) {
            return __('No expiry date');
        }

        return static::expiryStatusLabels()[$this->expiry_status] ?? (string) $this->expiry_status;
    }

    public function getExpiryStatusAttribute(): ?string
    {
        if ($this->ExpiryDate === null) {
            return null;
        }

        $today = now()->startOfDay();

        if ($this->ExpiryDate->lt($today)) {
            return self::EXPIRY_EXPIRED;
        }

        if ($this->ExpiryDate->lte($today->copy()->addDays(self::NEAR_EXPIRY_DAYS))) {
            return self::EXPIRY_NEAR;
        }

        return self::EXPIRY_OK;
    }

    public function scopeExpiryStatus(Builder $query, string $status): Builder
    {
        $today = now()->toDateString();
        $horizon = now()->addDays(self::NEAR_EXPIRY_DAYS)->toDateString();

        return match ($status) {
            self::EXPIRY_EXPIRED => $query->whereNotNull('ExpiryDate')->where('ExpiryDate', '<', $today),
            self::EXPIRY_NEAR => $query->whereBetween('ExpiryDate', [$today, $horizon]),
            self::EXPIRY_OK => $query->where('ExpiryDate', '>', $horizon),
            default => $query,
        };
    }
}
