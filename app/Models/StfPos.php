<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StfPos extends Model
{
    protected $table = 'StfPos';

    protected $primaryKey = 'StfPos_No';

    public $incrementing = false;

    protected $keyType = 'string';

    public $timestamps = false;

    protected $fillable = [
        'StfPos_No', 'Stf_No', 'Pos_No', 'CurrSalary', 'HrsPerWk', 'ContractType', 'PaymentType',
    ];

    public function stf(): BelongsTo
    {
        return $this->belongsTo(Stf::class, 'Stf_No', 'Stf_No');
    }

    public function pos(): BelongsTo
    {
        return $this->belongsTo(Pos::class, 'Pos_No', 'Pos_No');
    }
}
