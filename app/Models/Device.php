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

    /**
     * Get all humidity readings for this device.
     */
    public function humidities()
    {
        return $this->hasMany(Humidity::class, 'device_id');
    }

    /**
     * Get all soil pH readings for this device.
     */
    public function soilPHs()
    {
        return $this->hasMany(SoilPH::class, 'device_id');
    }

    public function controlActions()
    {
        return $this->hasMany(ControlActions::class, 'device_id');
    }
}
