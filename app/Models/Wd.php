<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Wd extends Model
{
    protected $table = 'Wd';

    protected $primaryKey = 'Wd_No';

    public $incrementing = false;

    protected $keyType = 'string';

    public $timestamps = false;

    protected $fillable = [
        'Wd_No', 'Wd_Name', 'Location', 'TotalBeds', 'TelExtension',
    ];

    public function rotas(): HasMany
    {
        return $this->hasMany(StfRota::class, 'Wd_No', 'Wd_No');
    }
}
