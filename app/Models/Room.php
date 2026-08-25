<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Room extends Model
{
    protected $table = 'Room';

    protected $primaryKey = 'Room_No';

    public $incrementing = false;

    protected $keyType = 'string';

    public $timestamps = false;

    protected $fillable = [
        'Room_No', 'RoomName', 'Location',
    ];

    public function appointments(): HasMany
    {
        return $this->hasMany(Appointment::class, 'Room_No', 'Room_No');
    }
}
