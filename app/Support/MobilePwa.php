<?php

namespace App\Support;

use Illuminate\Http\Request;

/**
 * Regras partilhadas da app instalável (PWA) da gestão mobile.
 *
 * A app abre sempre em /gestao/mobile (start_url do public/mobile-manifest.json).
 * Sem sessão, o Laravel guarda esse URL em "url.intended" e manda para o login;
 * estas funções dizem se esse URL é seguro (mesma origem) e se é da área mobile,
 * para o login e o RedirectIfAuthenticated poderem regressar lá sem abrir a
 * porta a redirecionamentos para fora do site.
 *
 * Isto só escolhe para onde regressar: o acesso continua a ser decidido no
 * servidor (auth, restrictAngariador, authorizeResource e mobile.access).
 */
final class MobilePwa
{
    public const START_PATH = '/gestao/mobile';

    /** O caminho pertence à área mobile (/gestao/mobile ou /gestao/mobile/...)? */
    public static function isMobilePath(string $path): bool
    {
        $path = '/' . ltrim($path, '/');

        return $path === self::START_PATH || str_starts_with($path, self::START_PATH . '/');
    }

    /**
     * O URL é da mesma origem que o pedido (ou que o APP_URL)? Caminhos relativos
     * só contam se começarem por uma única "/" (nada de "//host" nem "\").
     */
    public static function isSameOrigin(string $url, Request $request): bool
    {
        if ($url === '' || str_contains($url, '\\') || preg_match('/[\x00-\x1F\x7F]/', $url)) {
            return false;
        }

        $parts = parse_url($url);
        if ($parts === false) {
            return false;
        }

        // Credenciais no URL ("https://site@outro/..." ou "https://outro@site/..."): recusar sempre.
        if (isset($parts['user']) || isset($parts['pass'])) {
            return false;
        }

        if (!isset($parts['scheme']) && !isset($parts['host'])) {
            return str_starts_with($url, '/') && !str_starts_with($url, '//');
        }

        if (!isset($parts['scheme'], $parts['host']) || !in_array(strtolower($parts['scheme']), ['http', 'https'], true)) {
            return false;
        }

        $origin = strtolower($parts['scheme'] . '://' . $parts['host'] . (isset($parts['port']) ? ':' . $parts['port'] : ''));

        $allowed = [strtolower($request->getSchemeAndHttpHost())];
        if ($appUrl = config('app.url')) {
            $app = parse_url($appUrl);
            if (isset($app['scheme'], $app['host'])) {
                $allowed[] = strtolower($app['scheme'] . '://' . $app['host'] . (isset($app['port']) ? ':' . $app['port'] : ''));
            }
        }

        return in_array($origin, $allowed, true);
    }

    /** URL "intended" da sessão, se for da mesma origem e da área mobile; null caso contrário. */
    public static function intendedMobileUrl(Request $request): ?string
    {
        if (!$request->hasSession()) {
            return null;
        }

        $intended = $request->session()->get('url.intended');
        if (!is_string($intended) || !self::isSameOrigin($intended, $request)) {
            return null;
        }

        return self::isMobilePath((string) parse_url($intended, PHP_URL_PATH)) ? $intended : null;
    }

    /** Descarta um "url.intended" que aponte para fora do site (evita open redirects no login). */
    public static function forgetUnsafeIntendedUrl(Request $request): void
    {
        if (!$request->hasSession()) {
            return;
        }

        $intended = $request->session()->get('url.intended');
        if ($intended !== null && (!is_string($intended) || !self::isSameOrigin($intended, $request))) {
            $request->session()->forget('url.intended');
        }
    }
}
