<?php

namespace Tests\Feature;

use App\Services\GeocodingService;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use Illuminate\Support\Facades\Log;
use Tests\TestCase;

/** LOG-014: o erro deixa de parecer "morada não encontrada". */
class GeocodingServiceTest extends TestCase
{
    private function service(array $queue): GeocodingService
    {
        return new GeocodingService(new Client(['handler' => HandlerStack::create(new MockHandler($queue))]));
    }

    public function test_resultado_ok(): void
    {
        $s = $this->service([new Response(200, [], json_encode([['lat' => '38.7', 'lon' => '-9.1', 'display_name' => 'Lisboa']]))]);

        $r = $s->lookup('Lisboa');

        $this->assertSame('ok', $r['status']);
        $this->assertSame(['lat' => 38.7, 'lng' => -9.1, 'display_name' => 'Lisboa'], $r['data']);
    }

    public function test_sem_resultados_nao_e_erro_e_nao_regista_log(): void
    {
        Log::spy();
        $s = $this->service([new Response(200, [], '[]')]);

        $this->assertSame(['status' => 'not_found', 'data' => null], $s->lookup('Xyzzy'));
        Log::shouldNotHaveReceived('warning');
    }

    public function test_falha_de_rede_devolve_erro_e_regista_sem_a_morada(): void
    {
        Log::spy();
        $s = $this->service([new ConnectException('cURL error: https://nominatim/search?q=Rua+Segredo+12', new Request('GET', 'x'))]);

        $this->assertSame(['status' => 'error', 'data' => null], $s->lookup('Rua Segredo 12'));
        Log::shouldHaveReceived('warning')->once()->withArgs(function ($msg, $ctx) {
            return str_contains($msg, 'Geocoding') && ! str_contains(json_encode($ctx), 'Segredo');
        });
    }

    public function test_limite_429_e_resposta_invalida_sao_erro(): void
    {
        Log::spy();
        $s = $this->service([new Response(429, [], 'Too many'), new Response(200, [], 'não é json')]);

        $this->assertSame('error', $s->lookup('Porto')['status']);
        $this->assertSame('error', $s->lookup('Porto')['status']);
    }

    public function test_geocode_continua_a_devolver_null_sem_rebentar(): void
    {
        Log::spy();
        $s = $this->service([new Response(500, [], 'erro')]);

        $this->assertNull($s->geocode('Porto'));
        $this->assertNull($s->geocode('   '));
    }
}
