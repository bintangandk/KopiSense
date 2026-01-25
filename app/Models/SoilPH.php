<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SoilPH extends Model
{
    use HasFactory;

    protected $table = 'soil_ph';

    protected $fillable = [
        'device_id',
        'value_ph',
    ];

    /**
     * Get the device that owns the soil pH reading.
     */
    public function device()
    {
        return $this->belongsTo(Device::class, 'device_id');
    }
}
