<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ControlActions extends Model
{
    use HasFactory;

    protected $table = 'control_actions';

    protected $fillable = [
        'device_id',
        'pump_status',
        'mist_duration',
        'method',
    ];

    /**
     * Get the device that owns the control action.
     */

    public function device()
    {
        return $this->belongsTo(Device::class, 'device_id');
    }
}
