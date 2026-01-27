<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EnvironmentThreshold extends Model
{
    use HasFactory;

    protected $table = 'environment_thresholds';

    protected $fillable = [
        'temperature_min',
        'temperature_max',
        'humidity_min',
        'humidity_max',
        'soil_ph_min',
        'soil_ph_max',
    ];
}
