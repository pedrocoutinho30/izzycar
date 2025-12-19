<?php

namespace App\Http\Controllers;

use App\Models\Sale;
use App\Models\Vehicle;

use Illuminate\Http\Request;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     * Redireciona para a dashboard V2
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function index()
    {
        return redirect()->route('admin.v2.dashboard');
    }
}
