<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

/**
 * Formulários públicos: bloqueia envios de robôs com um honeypot (campo
 * invisível que só um robô preenche) e um tempo mínimo de preenchimento.
 * O robô recebe uma resposta de "sucesso" para não perceber o bloqueio —
 * nada é gravado. Os campos vêm do partial frontend.partials._honeypot.
 */
class BlockSpamSubmissions
{
    public const FIELD = 'company_website';
    public const TIME_FIELD = '_form_started';
    public const MIN_SECONDS = 2;

    public function handle(Request $request, Closure $next)
    {
        $startedAt = (int) $request->input(self::TIME_FIELD);
        $tooFast = $startedAt > 0 && (time() - $startedAt) < self::MIN_SECONDS;

        if (filled($request->input(self::FIELD)) || $tooFast) {
            Log::warning('Envio de formulário bloqueado (anti-spam)', [
                'route' => $request->route()?->getName(),
                'ip' => $request->ip(),
                'reason' => $tooFast ? 'too_fast' : 'honeypot',
            ]);

            return $request->expectsJson()
                ? response()->json(['success' => true, 'message' => 'Pedido enviado com sucesso!'])
                : back()->with('success', 'Pedido enviado com sucesso!');
        }

        return $next($request);
    }
}
