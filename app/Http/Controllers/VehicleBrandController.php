<?php

namespace App\Http\Controllers;

use App\Models\VehicleBrand;
use App\Models\VehicleModel;

class VehicleBrandController extends Controller
{
    public function index()
    {
        $brands = VehicleBrand::with('models')->get();
        return view('brands.index', compact('brands'));
    }
}
