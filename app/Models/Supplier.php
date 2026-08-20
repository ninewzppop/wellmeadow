<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Supplier extends Model
{
    protected $table = 'Supplier';

    protected $primaryKey = 'Suppl_No';

    public $incrementing = false;

    protected $keyType = 'string';

    public $timestamps = false;

    protected $fillable = [
        'Suppl_No', 'Name', 'Address', 'TelNo', 'FaxNo',
    ];
}
