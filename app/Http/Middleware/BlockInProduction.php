<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;

class BlockInProduction
{
    public function handle(Request $request, Closure $next)
    {
        if (App::environment('production')) {
        return response()->view('frontend.coming-soon');
        }

        return $next($request);
    }
}
