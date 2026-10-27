<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Services\ProposalService;
use Illuminate\Support\Facades\Mail;
use Tests\RefreshesDatabaseWithoutMysqlOnlyMigrations;
use Tests\TestCase;

class ProposalCodeTest extends TestCase
{
    use RefreshesDatabaseWithoutMysqlOnlyMigrations;

    public function test_codigo_da_cotacao_e_utf8_valido_com_iniciais_acentuadas(): void
    {
        Mail::fake();
        $client = Client::create(['name' => 'Ana', 'email' => 'ana@example.com', 'is_lead' => true, 'lead_status' => 'em_contacto']);
        $service = app(ProposalService::class);

        $codigos = [];
        foreach ([['Élan', 'Škoda'], ['Škoda', 'Élan'], ['bmw', 'x5']] as [$marca, $modelo]) {
            $codigos[] = $service->create([
                'client_id' => $client->id, 'brand' => $marca, 'model' => $modelo, 'transport_cost' => 800, 'status' => 'Pendente',
            ], null, null, null)->proposal_code;
        }

        foreach ($codigos as $codigo) {
            $this->assertTrue(mb_check_encoding($codigo, 'UTF-8'), $codigo);
            $this->assertSame(10, mb_strlen($codigo));
        }
        $this->assertStringStartsWith('ÉŠ', $codigos[0]);
        $this->assertStringStartsWith('ŠÉ', $codigos[1]);
        $this->assertStringStartsWith('BX', $codigos[2]);
    }
}
