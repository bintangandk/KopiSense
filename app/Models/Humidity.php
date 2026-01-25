<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Humidity extends Model
{
    use HasFactory;

    protected $table = 'humidities';

    protected $fillable = [
        'device_id',
        'value_humidity',
    ];

    /**
     * Get the device that owns the humidity reading.
     */
    public function device()
    {
        return $this->belongsTo(Device::class, 'device_id');
    }
}
