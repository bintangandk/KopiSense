<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Device extends Model
{
    use HasFactory;

    protected $table = 'devices';

    protected $fillable = [
        'device_id',
        'name',
        'latitude',
        'longitude',
    ];

    /**
     * Get all temperature readings for this device.
     */
    public function temperatures()
    {
        return $this->hasMany(Temperature::class, 'device_id');
    }
}
