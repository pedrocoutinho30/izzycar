<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Acrescenta a Content-Security-Policy construída a partir de config/security.php.
 * Por omissão em modo report-only (não bloqueia nada). Ver docs/seguranca-csp.md.
 */
class ContentSecurityPolicyReportOnly
{
    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);

        if (! config('security.csp.enabled', true) || ! $response instanceof Response) {
            return $response;
        }

        $header = config('security.csp.report_only', true)
            ? 'Content-Security-Policy-Report-Only'
            : 'Content-Security-Policy';

        $response->headers->set($header, self::buildPolicy());

        return $response;
    }

    public static function buildPolicy(): string
    {
        $parts = [];

        foreach ((array) config('security.csp.directives', []) as $directive => $sources) {
            $sources = array_filter(array_unique((array) $sources));
            $parts[] = trim($directive.' '.implode(' ', $sources));
        }

        if ($uri = config('security.csp.report_uri')) {
            $parts[] = 'report-uri '.$uri;
        }

        return implode('; ', $parts);
    }
}
