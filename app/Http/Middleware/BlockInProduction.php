<?php

namespace App\Http\Middleware;

use App\Models\Setting;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;

class BlockInProduction
{
    public function handle(Request $request, Closure $next)
    {

        $mantenanceMode = Setting::where('label', 'maintenance_mode')->first();
        if (App::environment('production') && $mantenanceMode && $mantenanceMode->value) {
            return response()->view('frontend.coming-soon');
        }

        return $next($request);
    }
}
