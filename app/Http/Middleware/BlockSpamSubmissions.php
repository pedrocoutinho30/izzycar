<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;

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
    public const MIN_SECONDS = 3;

    /** Um formulário aberto "no futuro" (relógio forjado) também é suspeito. */
    private const MAX_FUTURO_SEGUNDOS = 60;

    /** Limites por hora: mesmo email na mesma rota e mesmo IP em todos os formulários. */
    private const MAX_POR_EMAIL = 5;
    private const MAX_POR_IP = 60;

    public function handle(Request $request, Closure $next)
    {
        $motivo = $this->motivoDeBloqueio($request);

        if ($motivo !== null) {
            // Sem email nem outros dados pessoais no log: só rota, IP e motivo.
            Log::warning('Envio de formulário bloqueado (anti-spam)', [
                'route' => $request->route()?->getName(),
                'ip' => $request->ip(),
                'reason' => $motivo,
            ]);

            // Limites de pedidos: uma pessoa real também pode lá chegar (rede partilhada,
            // vários envios seguidos), por isso recebe a verdade em vez de um falso sucesso.
            if (in_array($motivo, ['ip_limit', 'email_limit'], true)) {
                $aviso = 'Enviou muitos pedidos seguidos. Aguarde alguns minutos e tente novamente, ou contacte-nos por telefone.';

                return $request->expectsJson()
                    ? response()->json(['status' => 'error', 'message' => $aviso], 429)
                    : back()->withErrors(['limite' => $aviso]);
            }

            // Robôs: resposta de sucesso (inclui "status", que o JS dos formulários espera).
            return $request->expectsJson()
                ? response()->json(['success' => true, 'status' => 'success', 'message' => 'Pedido enviado com sucesso!'])
                : back()->with('success', 'Pedido enviado com sucesso!');
        }

        return $next($request);
    }

    private function motivoDeBloqueio(Request $request): ?string
    {
        if (filled($request->input(self::FIELD))) {
            return 'honeypot';
        }

        // A hora de abertura tem de existir (o partial _honeypot envia-a sempre):
        // omitir o campo já não contorna a verificação.
        $startedAt = $request->input(self::TIME_FIELD);
        if (! is_numeric($startedAt) || (int) $startedAt <= 0) {
            return 'missing_time';
        }

        $decorrido = time() - (int) $startedAt;
        if ($decorrido < self::MIN_SECONDS) {
            return $decorrido < -self::MAX_FUTURO_SEGUNDOS ? 'bad_time' : 'too_fast';
        }

        // Limite por IP (todos os formulários) e por email (por rota), à hora.
        // A chave do email é um hash: o email não fica na cache em claro.
        if (RateLimiter::tooManyAttempts($chaveIp = 'antispam:ip:' . $request->ip(), self::MAX_POR_IP)) {
            return 'ip_limit';
        }
        RateLimiter::hit($chaveIp, 3600);

        $email = $request->input('email');
        if (is_string($email) && filled($email)) {
            $chaveEmail = 'antispam:email:' . ($request->route()?->getName() ?? $request->path())
                . ':' . sha1(mb_strtolower(trim($email)));
            if (RateLimiter::tooManyAttempts($chaveEmail, self::MAX_POR_EMAIL)) {
                return 'email_limit';
            }
            RateLimiter::hit($chaveEmail, 3600);
        }

        return null;
    }
}
