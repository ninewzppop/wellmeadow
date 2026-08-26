<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Medications extends Model
{
    protected $table = 'Medications';

    protected $primaryKey = 'Med_No';

    public $incrementing = false;

    protected $keyType = 'string';

    public $timestamps = false;

    protected $fillable = [
        'Med_No', 'Pt_No', 'Stf_No', 'Drug_No', 'UnitsPerDay', 'AdminMethod', 'StartDate', 'FinishDate',
    ];

    protected $casts = [
        'StartDate' => 'date',
        'FinishDate' => 'date',
    ];

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class, 'Pt_No', 'Pt_No');
    }

    public function staff(): BelongsTo
    {
        return $this->belongsTo(Stf::class, 'Stf_No', 'Stf_No');
    }

    public function drug(): BelongsTo
    {
        return $this->belongsTo(Pharmaceutical::class, 'Drug_No', 'Drug_No');
    }

    public static function nextNo(): string
    {
        $max = static::query()
            ->where('Med_No', 'like', 'MD%')
            ->pluck('Med_No')
            ->map(fn (string $medNo) => (int) substr($medNo, 2))
            ->max();

        return 'MD'.($max + 1);
    }
}
