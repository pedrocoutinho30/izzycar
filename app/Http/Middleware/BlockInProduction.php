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
        // Cache única das definições (invalidada ao gravar): evita uma query por pedido quando o valor é nulo.
        $maintenanceValue = Setting::cachedValue('maintenance_mode');

        if (App::environment('production') && $maintenanceValue) {
            return response()->view('frontend.coming-soon');
        }

        return $next($request);
    }
}
