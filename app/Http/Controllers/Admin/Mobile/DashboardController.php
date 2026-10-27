<?php

namespace App\Http\Controllers\Admin\Mobile;

use App\Http\Controllers\Controller;
use App\Services\MobileDashboardService;

class DashboardController extends Controller
{
    public function index(MobileDashboardService $dashboard)
    {
        return view('admin.mobile.dashboard', [
            'grupos' => $dashboard->grupos(auth()->user()),
        ]);
    }
}
