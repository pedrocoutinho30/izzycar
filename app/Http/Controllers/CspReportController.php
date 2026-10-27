<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

/**
 * Recebe relatórios de violação da CSP (application/csp-report e
 * application/reports+json) e regista-os no canal "csp".
 */
class CspReportController extends Controller
{
    private const MAX_BYTES = 8192;

    private const NOISE = ['chrome-extension', 'moz-extension', 'safari-extension', 'safari-web-extension'];

    public function __invoke(Request $request)
    {
        $raw = $request->getContent();

        if ($raw === '' || strlen($raw) > self::MAX_BYTES) {
            return response()->noContent();
        }

        $data = json_decode($raw, true);
        if (! is_array($data)) {
            return response()->noContent();
        }

        // csp-report: {"csp-report": {...}}; reports+json: [{"type":"csp-violation","body":{...}}]
        $reports = isset($data['csp-report']) ? [$data['csp-report']] : [];
        if (! $reports && array_is_list($data)) {
            foreach ($data as $item) {
                if (is_array($item) && isset($item['body']) && is_array($item['body'])) {
                    $reports[] = $item['body'];
                }
            }
        }

        foreach (array_slice($reports, 0, 5) as $r) {
            if (! is_array($r)) {
                continue;
            }

            $blocked = $this->str($r['blocked-uri'] ?? $r['blockedURL'] ?? $r['blockedURI'] ?? '');
            $source = $this->str($r['source-file'] ?? $r['sourceFile'] ?? '');

            if ($this->isNoise($blocked) || $this->isNoise($source)) {
                continue;
            }

            Log::channel('csp')->warning('csp-violation', [
                'blocked-uri' => $blocked,
                'violated-directive' => $this->str($r['violated-directive'] ?? $r['effectiveDirective'] ?? $r['effective-directive'] ?? ''),
                'document-uri' => $this->stripQuery($this->str($r['document-uri'] ?? $r['documentURL'] ?? $r['documentURI'] ?? '')),
                'source-file' => $this->stripQuery($source),
            ]);
        }

        return response()->noContent();
    }

    private function str(mixed $v): string
    {
        return is_scalar($v) ? mb_substr((string) $v, 0, 300) : '';
    }

    private function stripQuery(string $url): string
    {
        return preg_replace('/[?#].*$/', '', $url);
    }

    private function isNoise(string $uri): bool
    {
        foreach (self::NOISE as $scheme) {
            if (str_starts_with($uri, $scheme.'://') || str_starts_with($uri, $scheme.':')) {
                return true;
            }
        }

        return false;
    }
}
