<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MedicationOrder extends Model
{
    public const STATUS_PENDING = 'pending';

    public const STATUS_DISPENSED = 'dispensed';

    public const STATUS_CANCELLED = 'cancelled';

    protected $table = 'MedicationOrder';

    protected $primaryKey = 'Order_No';

    public $incrementing = false;

    protected $keyType = 'string';

    public $timestamps = false;

    protected $fillable = [
        'Order_No', 'Pt_No', 'Stf_No', 'Appt_No', 'status', 'OrderedAt', 'PaidAt', 'DispensedAt', 'CancelledAt', 'CancelReason',
    ];

    protected $casts = [
        'OrderedAt' => 'datetime',
        'PaidAt' => 'datetime',
        'DispensedAt' => 'datetime',
        'CancelledAt' => 'datetime',
    ];

    public function items(): HasMany
    {
        return $this->hasMany(MedicationOrderItem::class, 'Order_No', 'Order_No');
    }

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class, 'Pt_No', 'Pt_No');
    }

    public function prescriber(): BelongsTo
    {
        return $this->belongsTo(Stf::class, 'Stf_No', 'Stf_No');
    }

    public function appointment(): BelongsTo
    {
        return $this->belongsTo(Appointment::class, 'Appt_No', 'Appt_No');
    }

    public static function nextNo(): string
    {
        $max = static::query()
            ->where('Order_No', 'like', 'MO%')
            ->pluck('Order_No')
            ->map(fn (string $orderNo) => (int) substr($orderNo, 2))
            ->max();

        return 'MO'.($max + 1);
    }
}
