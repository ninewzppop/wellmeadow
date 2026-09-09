<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Supplier extends Model
{
    protected $table = 'Supplier';

    protected $primaryKey = 'Suppl_No';

    public $incrementing = false;

    protected $keyType = 'string';

    public $timestamps = false;

    public function getRouteKeyName(): string
    {
        return 'Suppl_No';
    }

    protected $fillable = [
        'Suppl_No', 'Name', 'Address', 'TelNo', 'FaxNo',
    ];

    /**
     * Next auto-generated supplier number (SUP01, SUP02, …).
     */
    public static function nextNo(): string
    {
        $max = static::query()
            ->where('Suppl_No', 'like', 'SUP%')
            ->pluck('Suppl_No')
            ->map(fn (string $supplNo) => (int) substr($supplNo, 3))
            ->max();

        return 'SUP'.str_pad((string) (($max ?? 0) + 1), 2, '0', STR_PAD_LEFT);
    }
}
