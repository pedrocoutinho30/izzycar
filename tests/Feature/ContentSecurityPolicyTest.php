<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Log;
use Tests\RefreshesDatabaseWithoutMysqlOnlyMigrations;
use Tests\TestCase;

class ContentSecurityPolicyTest extends TestCase
{
    use RefreshesDatabaseWithoutMysqlOnlyMigrations;

    private string $logFile;

    protected function setUp(): void
    {
        parent::setUp();

        $this->logFile = tempnam(sys_get_temp_dir(), 'csp');
        config(['logging.channels.csp' => ['driver' => 'single', 'path' => $this->logFile, 'level' => 'debug']]);
        Log::forgetChannel('csp');
    }

    protected function tearDown(): void
    {
        @unlink($this->logFile);
        parent::tearDown();
    }

    private function report(array $body, string $type = 'application/csp-report')
    {
        return $this->call('POST', '/csp-report', [], [], [], ['CONTENT_TYPE' => $type], json_encode($body));
    }

    private function logged(): string
    {
        return (string) file_get_contents($this->logFile);
    }

    public function test_pagina_publica_devolve_cabecalho_report_only(): void
    {
        $response = $this->get('/login');

        $response->assertHeader('Content-Security-Policy-Report-Only');
        $this->assertFalse($response->headers->has('Content-Security-Policy'));

        $csp = $response->headers->get('Content-Security-Policy-Report-Only');
        foreach ([
            "default-src 'self'", 'script-src', 'style-src', 'font-src', 'img-src', 'connect-src', 'frame-src',
            "frame-ancestors 'self'", "base-uri 'self'", "form-action 'self'", "object-src 'none'",
            'report-uri /csp-report', 'https://cdn.jsdelivr.net', 'data: blob: https:',
        ] as $expected) {
            $this->assertStringContainsString($expected, $csp);
        }
        $this->assertStringNotContainsString('upgrade-insecure-requests', $csp);
    }

    public function test_modo_bloqueio_troca_o_cabecalho(): void
    {
        config(['security.csp.report_only' => false]);

        $response = $this->get('/login');

        $response->assertHeader('Content-Security-Policy');
        $this->assertFalse($response->headers->has('Content-Security-Policy-Report-Only'));
    }

    public function test_desativado_nao_emite_cabecalhos(): void
    {
        config(['security.csp.enabled' => false]);

        $response = $this->get('/login');

        $this->assertFalse($response->headers->has('Content-Security-Policy'));
        $this->assertFalse($response->headers->has('Content-Security-Policy-Report-Only'));
    }

    public function test_relatorio_valido_e_registado_sem_query_string(): void
    {
        $this->report(['csp-report' => [
            'document-uri' => 'https://izzycar.pt/viaturas?token=segredo',
            'blocked-uri' => 'https://evil.example/x.js',
            'violated-directive' => 'script-src-elem',
            'source-file' => 'https://izzycar.pt/js/a.js?v=1',
        ]])->assertNoContent();

        $log = $this->logged();
        $this->assertStringContainsString('https://evil.example/x.js', $log);
        $this->assertStringContainsString('script-src-elem', $log);
        $this->assertStringContainsString('https://izzycar.pt/viaturas', $log);
        $this->assertStringNotContainsString('segredo', $log);
        $this->assertStringNotContainsString('v=1', $log);
    }

    public function test_formato_reports_json_e_aceite(): void
    {
        $this->report([[
            'type' => 'csp-violation',
            'body' => [
                'documentURL' => 'https://izzycar.pt/',
                'blockedURL' => 'https://cdn.outro.example/y.css',
                'effectiveDirective' => 'style-src-elem',
            ],
        ]], 'application/reports+json')->assertNoContent();

        $this->assertStringContainsString('https://cdn.outro.example/y.css', $this->logged());
    }

    public function test_corpo_gigante_e_descartado(): void
    {
        $this->report(['csp-report' => [
            'blocked-uri' => 'https://gigante.example/',
            'violated-directive' => 'script-src',
            'padding' => str_repeat('a', 9000),
        ]])->assertNoContent();

        $this->assertSame('', $this->logged());
    }

    public function test_ruido_de_extensoes_e_ignorado(): void
    {
        foreach (['chrome-extension://abc/x.js', 'moz-extension://abc/x.js', 'safari-extension://abc/x.js'] as $uri) {
            $this->report(['csp-report' => [
                'blocked-uri' => $uri,
                'violated-directive' => 'script-src',
                'document-uri' => 'https://izzycar.pt/',
            ]])->assertNoContent();
        }

        $this->assertSame('', $this->logged());
    }

    public function test_json_invalido_devolve_204_sem_registar(): void
    {
        $this->call('POST', '/csp-report', [], [], [], ['CONTENT_TYPE' => 'application/csp-report'], 'isto nao e json')
            ->assertNoContent();

        $this->assertSame('', $this->logged());
    }
}
