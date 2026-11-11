<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

/**
 * Protege o endpoint POST /api/lead com um token fixo (LEAD_API_TOKEN no .env),
 * enviado em "Authorization: Bearer <token>" ou no cabeçalho "X-Api-Key".
 * Sem token configurado, o endpoint fica fechado.
 */
class VerifyLeadApiToken
{
    public function handle(Request $request, Closure $next)
    {
        $esperado = (string) config('services.lead_api.token');
        $enviado = (string) ($request->bearerToken() ?: $request->header('X-Api-Key', ''));

        if ($esperado === '' || $enviado === '' || ! hash_equals($esperado, $enviado)) {
            return response()->json(['status' => 'error', 'message' => 'Não autorizado.'], 401);
        }

        return $next($request);
    }
}
