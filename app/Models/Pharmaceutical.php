<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Pharmaceutical extends Model
{
    protected $table = 'Pharmaceutical';

    protected $primaryKey = 'Drug_No';

    public $incrementing = false;

    protected $keyType = 'string';

    public $timestamps = false;

    protected $fillable = [
        'Drug_No', 'Name', 'Description', 'Dosage', 'AdminMethod', 'QtyInStock', 'ReorderLvl', 'CostPerUnit', 'Suppl_No',
    ];

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class, 'Suppl_No', 'Suppl_No');
    }
}
