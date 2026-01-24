<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserProfile extends Model
{
    use HasFactory;

    protected $table = 'user_profiles';

    protected $fillable = [
        'user_id',
        'full_name',
        'nik',
        'gender',
        'phone',
        'address',
        'province_id',
        'city_id',
        'postal_code',
    ];

    /**
     * Get the user that owns the profile.
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id_users');
    }

    /**
     * Get the province of the user.
     */
    public function province()
    {
        return $this->belongsTo(Province::class, 'province_id');
    }

    /**
     * Get the city of the user.
     */
    public function city()
    {
        return $this->belongsTo(City::class, 'city_id');
    }
}
