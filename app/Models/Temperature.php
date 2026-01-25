<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Temperature extends Model
{
    use HasFactory;

    protected $table = 'temperatures';

    protected $fillable = [
        'device_id',
        'value_temp',
    ];

    /**
     * Get the device that owns the temperature reading.
     */
    public function device()
    {
        return $this->belongsTo(Device::class, 'device_id');
    }
}
