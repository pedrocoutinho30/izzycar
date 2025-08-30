<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Brand;

class BrandController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }
    public function index(Request $request)
    {

        $brands = Brand::with(['models' => function ($query) {
            $query->orderBy('name');
        }])->get();



        if ($request->filled('brand')) {
            $brands = $brands->where('name', $request->brand);
        }



        return view('brands.index', compact('brands'));
    }
}
