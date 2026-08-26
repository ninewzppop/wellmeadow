<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InPatient extends Model
{
    protected $table = 'InPatient';

    protected $primaryKey = 'In_Pt_No';

    public $incrementing = false;

    protected $keyType = 'string';

    public $timestamps = false;

    protected $fillable = [
        'In_Pt_No', 'Pt_No', 'Bed_No', 'DateWaitList', 'ExpStayDays', 'DatePlaced', 'DateLeave', 'ActDateLeft',
    ];

    protected $casts = [
        'DateWaitList' => 'date',
        'DatePlaced' => 'date',
        'DateLeave' => 'date',
        'ActDateLeft' => 'date',
    ];

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class, 'Pt_No', 'Pt_No');
    }

    public function bed(): BelongsTo
    {
        return $this->belongsTo(Bed::class, 'Bed_No', 'Bed_No');
    }

    public static function nextNo(): string
    {
        $max = static::query()
            ->where('In_Pt_No', 'like', 'IP%')
            ->pluck('In_Pt_No')
            ->map(fn (string $inPtNo) => (int) substr($inPtNo, 2))
            ->max();

        return 'IP'.($max + 1);
    }
}
