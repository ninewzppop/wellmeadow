<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Pos extends Model
{
    protected $table = 'Pos';

    protected $primaryKey = 'Pos_No';

    public $incrementing = false;

    protected $keyType = 'string';

    public $timestamps = false;

    protected $fillable = [
        'Pos_No', 'Pos_Name', 'SalaryScale',
    ];

    public function staffPositions(): HasMany
    {
        return $this->hasMany(StfPos::class, 'Pos_No', 'Pos_No');
    }
}
