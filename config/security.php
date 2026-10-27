<?php

/*
|--------------------------------------------------------------------------
| Content-Security-Policy
|--------------------------------------------------------------------------
|
| Ver docs/seguranca-csp.md. A política é construída por
| App\Http\Middleware\ContentSecurityPolicyReportOnly a partir de 'directives'.
|
| - enabled:     CSP_ENABLED (omissão true) liga/desliga o cabeçalho.
| - report_only: CSP_REPORT_ONLY (omissão true). Em true só regista violações
|                (Content-Security-Policy-Report-Only); em false BLOQUEIA
|                (Content-Security-Policy).
|
| LIMITAÇÃO CONHECIDA: o projeto usa muitos <script>/<style> inline e atributos
| on*="" , por isso script-src e style-src têm 'unsafe-inline'. A resolver mais
| tarde com nonces (e a mover handlers on*= para addEventListener).
*/

return [
    'csp' => [
        'enabled' => env('CSP_ENABLED', true),
        'report_only' => env('CSP_REPORT_ONLY', true),
        'report_uri' => '/csp-report',

        // Cada diretiva: lista de fontes. Palavras-chave entre aspas simples.
        'directives' => [
            'default-src' => ["'self'"],

            'script-src' => [
                "'self'",
                "'unsafe-inline'", // inline inevitável nesta fase
                'https://cdn.jsdelivr.net',
                'https://cdnjs.cloudflare.com',
                'https://unpkg.com',
                'https://cdn.ckeditor.com',
                'https://code.jquery.com',
                'https://www.googletagmanager.com',
                'https://static.hotjar.com',
                'https://script.hotjar.com',
                'https://maps.googleapis.com',
            ],

            'style-src' => [
                "'self'",
                "'unsafe-inline'", // style="" e <style> inline inevitáveis
                'https://fonts.googleapis.com',
                'https://cdn.jsdelivr.net',
                'https://cdnjs.cloudflare.com',
                'https://unpkg.com',
                'https://cdn.ckeditor.com',
            ],

            'font-src' => [
                "'self'",
                'data:',
                'https://fonts.gstatic.com',
                'https://cdn.jsdelivr.net',
                'https://cdnjs.cloudflare.com',
            ],

            // https: aberto por agora (ui-avatars, placeholders, Google Maps, GA pixels...)
            'img-src' => ["'self'", 'data:', 'blob:', 'https:'],

            'connect-src' => [
                "'self'",
                'https://www.google-analytics.com',
                'https://region1.google-analytics.com',
                'https://analytics.google.com',
                'https://www.googletagmanager.com',
                'https://*.hotjar.com',
                'https://*.hotjar.io',
                'wss://*.hotjar.com',
                'https://maps.googleapis.com',
                'https://cdn.ckeditor.com',
                'https://cdn.jsdelivr.net', // source maps das bibliotecas
            ],

            'frame-src' => [
                "'self'",
                'https://www.google.com',
                'https://vars.hotjar.com',
            ],

            'frame-ancestors' => ["'self'"],
            'base-uri' => ["'self'"],
            'form-action' => ["'self'"],
            'object-src' => ["'none'"],
        ],
    ],
];
