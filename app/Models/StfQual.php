<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StfQual extends Model
{
    protected $table = 'StfQual';

    protected $primaryKey = 'Qual_No';

    public $incrementing = false;

    protected $keyType = 'string';

    public $timestamps = false;

    protected $fillable = [
        'Qual_No', 'Stf_No', 'Type', 'QualDate', 'Institution',
    ];

    protected $casts = [
        'QualDate' => 'date',
    ];

    public function stf(): BelongsTo
    {
        return $this->belongsTo(Stf::class, 'Stf_No', 'Stf_No');
    }
}
