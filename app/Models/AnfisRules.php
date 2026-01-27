<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AnfisRules extends Model
{
    use HasFactory;

    protected $table = 'anfis_rules';

    protected $fillable = [
        'temperature_category',
        'humidity_category',
        'soil_ph_category',
        'pump_action',
    ];
}
