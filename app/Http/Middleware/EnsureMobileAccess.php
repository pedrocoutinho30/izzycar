<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

/**
 * A área /gestao/mobile só é acessível a quem tem access_mobile. Sem acesso
 * volta ao desktop (rota fora do grupo mobile, logo sem ciclo de redirects).
 */
class EnsureMobileAccess
{
    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();

        if (!$user || !$user->access_mobile) {
            return redirect()->route('admin.v2.dashboard')
                ->with('error', 'Não tem acesso à versão mobile.');
        }

        return $next($request);
    }
}
