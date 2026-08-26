<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Wardrequisition extends Model
{
    protected $table = 'Wardrequisitions';

    protected $primaryKey = 'Wd_Req_No';

    public $incrementing = false;

    protected $keyType = 'string';

    public $timestamps = false;

    protected $fillable = [
        'Wd_Req_No', 'Stf_No', 'Wd_No', 'DateOrd', 'DateRecv', 'status', 'Received_By',
    ];

    protected $casts = [
        'DateOrd' => 'date',
        'DateRecv' => 'date',
    ];

    public const STATUS_PENDING = 'Pending';

    public const STATUS_APPROVED = 'Approved';

    public const STATUS_COMPLETED = 'Completed';

    public function requester(): BelongsTo
    {
        return $this->belongsTo(Stf::class, 'Stf_No', 'Stf_No');
    }

    public function ward(): BelongsTo
    {
        return $this->belongsTo(Wd::class, 'Wd_No', 'Wd_No');
    }

    public function receiver(): BelongsTo
    {
        return $this->belongsTo(Stf::class, 'Received_By', 'Stf_No');
    }

    public function itemRequests(): HasMany
    {
        return $this->hasMany(Itemrequest::class, 'Wd_Req_No', 'Wd_Req_No');
    }

    public function drugRequests(): HasMany
    {
        return $this->hasMany(Drugrequest::class, 'Wd_Req_No', 'Wd_Req_No');
    }

    public static function nextNo(): string
    {
        $max = static::query()
            ->where('Wd_Req_No', 'like', 'WR%')
            ->pluck('Wd_Req_No')
            ->map(fn (string $no) => (int) substr($no, 2))
            ->max();

        return 'WR'.($max + 1);
    }
}
