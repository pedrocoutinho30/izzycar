<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

/**
 * Confirma que o pedido POST ao webhook vem mesmo da Meta, validando o HMAC
 * SHA-256 do corpo do pedido (calculado com o App Secret) contra o header
 * X-Hub-Signature-256.
 */
class VerifyWhatsAppSignature
{
    public function handle(Request $request, Closure $next)
    {
        $header = $request->header('X-Hub-Signature-256', '');
        $signature = str_starts_with($header, 'sha256=') ? substr($header, 7) : $header;

        $expected = hash_hmac('sha256', $request->getContent(), (string) config('services.whatsapp.app_secret'));

        if (! $signature || ! hash_equals($expected, $signature)) {
            Log::warning('WhatsApp webhook: assinatura inválida', ['ip' => $request->ip()]);

            return response()->json(['error' => 'invalid signature'], 403);
        }

        return $next($request);
    }
}
