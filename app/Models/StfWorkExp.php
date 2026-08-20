<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StfWorkExp extends Model
{
    protected $table = 'StfWorkExp';

    protected $primaryKey = 'WorkExp_No';

    public $incrementing = false;

    protected $keyType = 'string';

    public $timestamps = false;

    protected $fillable = [
        'WorkExp_No', 'Stf_No', 'Organization', 'Position', 'StartDate', 'FinishDate',
    ];

    protected $casts = [
        'StartDate' => 'date',
        'FinishDate' => 'date',
    ];

    public function stf(): BelongsTo
    {
        return $this->belongsTo(Stf::class, 'Stf_No', 'Stf_No');
    }
}
