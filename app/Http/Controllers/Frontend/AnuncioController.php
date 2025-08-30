<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class FrontendAnuncioController extends Controller
{
  
    public function index()
    {

        $vehicles = \App\Models\Vehicle::where('show_online', true)->get();
        $marcas = \App\Models\Brand::all();
        return view('anuncios-frontend.index', compact('vehicles', 'marcas'));
    }

}
