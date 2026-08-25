<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Appointment extends Model
{
    public const STATUS_WAITING = 'waiting list';

    public const STATUS_SCHEDULED = 'scheduled';

    public const STATUS_IN_CONSULTATION = 'in consultation';

    public const STATUS_COMPLETED_MEDICATION = 'completed-medication';

    public const STATUS_COMPLETED_WAITLIST = 'completed-waitlist';

    public const STATUS_COMPLETED = 'completed';

    public const ACTIVE_STATUSES = [
        self::STATUS_WAITING,
        self::STATUS_SCHEDULED,
        self::STATUS_IN_CONSULTATION,
    ];

    public const COMPLETED_STATUSES = [
        self::STATUS_COMPLETED_MEDICATION,
        self::STATUS_COMPLETED_WAITLIST,
        self::STATUS_COMPLETED,
    ];

    protected $table = 'Appointment';

    protected $primaryKey = 'Appt_No';

    public $incrementing = false;

    protected $keyType = 'string';

    public $timestamps = false;

    protected $fillable = [
        'Appt_No', 'Pt_No', 'Consult_Stf_No', 'ApptDate', 'ApptTime', 'Room_No', 'status',
    ];

    protected $casts = [
        'ApptDate' => 'date',
        'ApptTime' => 'datetime:H:i',
    ];

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class, 'Pt_No', 'Pt_No');
    }

    public function consultant(): BelongsTo
    {
        return $this->belongsTo(Stf::class, 'Consult_Stf_No', 'Stf_No');
    }

    public function room(): BelongsTo
    {
        return $this->belongsTo(Room::class, 'Room_No', 'Room_No');
    }

    public function outpatient(): HasOne
    {
        return $this->hasOne(Outpatient::class, 'Appt_out_No', 'Appt_No');
    }

    public static function statusLabels(): array
    {
        return [
            self::STATUS_WAITING => __('Waiting for consultation'),
            self::STATUS_SCHEDULED => __('Scheduled'),
            self::STATUS_IN_CONSULTATION => __('In consultation'),
            self::STATUS_COMPLETED_MEDICATION => __('Completed - Medication Dispensed'),
            self::STATUS_COMPLETED_WAITLIST => __('Completed - Admitted to Waiting List'),
            self::STATUS_COMPLETED => __('Completed'),
            'cancelled' => __('Cancelled'),
            'no-show' => __('No show'),
        ];
    }

    public function statusLabel(): string
    {
        return static::statusLabels()[$this->status] ?? ucfirst(str_replace('-', ' ', (string) $this->status));
    }

    public function isActive(): bool
    {
        return in_array($this->status, static::ACTIVE_STATUSES, true);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->whereIn('status', static::ACTIVE_STATUSES);
    }

    public function scopeCompleted(Builder $query): Builder
    {
        return $query->whereIn('status', static::COMPLETED_STATUSES);
    }
}
