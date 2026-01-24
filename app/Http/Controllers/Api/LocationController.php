<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Province;
use Illuminate\Http\Request;

class LocationController extends Controller
{
    /**
     * Get all provinces
     */
    public function getProvinces()
    {
        $provinces = Province::all();
        return response()->json($provinces);
    }

    /**
     * Get cities by province ID
     */
    public function getCitiesByProvince($provinceId)
    {
        $cities = Province::find($provinceId)?->cities ?? [];
        return response()->json($cities);
    }
}
