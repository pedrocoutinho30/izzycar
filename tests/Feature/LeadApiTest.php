<?php

namespace Tests\Feature;

use App\Models\Client;
use Tests\RefreshesDatabaseWithoutMysqlOnlyMigrations;
use Tests\TestCase;

class LeadApiTest extends TestCase
{
    use RefreshesDatabaseWithoutMysqlOnlyMigrations;

    protected function setUp(): void
    {
        parent::setUp();
        config(['services.lead_api.token' => 'segredo-de-teste']);
    }

    private function enviar(array $dados, ?string $token = 'segredo-de-teste')
    {
        return $this->postJson('/api/lead', $dados, $token ? ['Authorization' => "Bearer {$token}"] : []);
    }

    public function test_sem_token_ou_token_errado_da_401(): void
    {
        $this->enviar(['name' => 'Ana', 'phone' => '912345678'], null)->assertStatus(401);
        $this->enviar(['name' => 'Ana', 'phone' => '912345678'], 'errado')->assertStatus(401);
        $this->assertSame(0, Client::count());
    }

    public function test_sem_token_configurado_o_endpoint_fica_fechado(): void
    {
        config(['services.lead_api.token' => '']);
        $this->enviar(['name' => 'Ana', 'phone' => '912345678'], '')->assertStatus(401);
    }

    public function test_cria_lead_com_origem_evexperience_por_omissao(): void
    {
        $this->enviar(['name' => 'Ana', 'phone' => '912345678', 'message' => 'Quero um EV'])
            ->assertStatus(201)->assertJson(['status' => 'success', 'created' => true]);

        $c = Client::first();
        $this->assertTrue($c->is_lead);
        $this->assertSame('EVEXPERIENCE', $c->lead_source);
        $this->assertSame('nova', $c->lead_status);
    }

    public function test_aceita_x_api_key_e_origem_no_pedido(): void
    {
        $this->postJson('/api/lead', ['name' => 'Rui', 'email' => 'rui@example.com', 'source' => 'OUTRA'], ['X-Api-Key' => 'segredo-de-teste'])
            ->assertStatus(201);
        $this->assertSame('OUTRA', Client::first()->lead_source);
    }

    public function test_exige_nome_e_pelo_menos_telefone_ou_email(): void
    {
        $this->enviar(['phone' => '912345678'])->assertStatus(422)->assertJsonValidationErrors('name');
        $this->enviar(['name' => 'Ana'])->assertStatus(422)->assertJsonValidationErrors(['phone', 'email']);
        $this->enviar(['name' => 'Ana', 'email' => 'invalido'])->assertStatus(422)->assertJsonValidationErrors('email');
    }

    public function test_contacto_repetido_reaproveita_o_registo(): void
    {
        $this->enviar(['name' => 'Ana', 'phone' => '912345678'])->assertStatus(201);
        $this->enviar(['name' => 'Ana', 'phone' => '+351 912 345 678'])->assertStatus(200)->assertJson(['created' => false]);
        $this->assertSame(1, Client::count());
    }
}
