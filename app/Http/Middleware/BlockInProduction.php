<?php

namespace App\Http\Middleware;

use App\Models\Setting;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Cache;

class BlockInProduction
{
    public function handle(Request $request, Closure $next)
    {
        $maintenanceValue = Cache::remember('maintenance_mode', 300, function () {
            $setting = Setting::where('label', 'maintenance_mode')->first();
            return $setting ? $setting->value : null;
        });

        if (App::environment('production') && $maintenanceValue) {
            return response()->view('frontend.coming-soon');
        }

        return $next($request);
    }
}
