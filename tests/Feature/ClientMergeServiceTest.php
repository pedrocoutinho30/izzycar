<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\Proposal;
use App\Services\ClientMergeService;
use Illuminate\Support\Facades\DB;
use Tests\RefreshesDatabaseWithoutMysqlOnlyMigrations;
use Tests\TestCase;

/** LOG-012: contagens e movimentos dentro da mesma transação. */
class ClientMergeServiceTest extends TestCase
{
    use RefreshesDatabaseWithoutMysqlOnlyMigrations;

    private function client(string $name, array $extra = []): Client
    {
        return Client::create($extra + ['name' => $name, 'is_lead' => false, 'lead_status' => 'nova']);
    }

    private function proposal(Client $c, string $code): Proposal
    {
        return Proposal::create(['client_id' => $c->id, 'brand' => 'BMW', 'model' => 'i4', 'transport_cost' => 0, 'proposal_code' => $code, 'status' => 'Pendente']);
    }

    public function test_simulacao_nao_altera_nada_e_conta_o_que_se_move(): void
    {
        $keep = $this->client('Maria');
        $dup = $this->client('Maria S.', ['email' => 'maria@example.com']);
        $this->proposal($dup, 'DUP1');
        $this->proposal($dup, 'DUP2');

        $r = (new ClientMergeService)->merge($keep, collect([$dup]), false);

        $this->assertSame(2, $r['moved']['proposals']);
        $this->assertContains('email', $r['filled']);
        $this->assertSame(2, Proposal::where('client_id', $dup->id)->count());
        $this->assertNotNull(Client::find($dup->id));
    }

    public function test_aplicar_move_tudo_e_o_relatorio_coincide_com_o_movido(): void
    {
        $keep = $this->client('Maria');
        $dup = $this->client('Maria S.', ['email' => 'maria@example.com']);
        $this->proposal($dup, 'DUP1');
        $this->proposal($keep, 'KEEP1');

        $r = (new ClientMergeService)->merge($keep, collect([$dup]), true);

        $this->assertSame(1, $r['moved']['proposals']);
        $this->assertSame(2, Proposal::where('client_id', $keep->id)->count());
        $this->assertNull(Client::find($dup->id));
        // A instância recebida reflete o resultado.
        $this->assertSame('maria@example.com', $keep->email);
        $this->assertSame('maria@example.com', $keep->fresh()->email);
    }

    public function test_falha_a_meio_reverte_tudo(): void
    {
        $keep = $this->client('Maria');
        $dup = $this->client('Maria S.');
        $this->proposal($dup, 'DUP1');

        // Falha injetada depois de mover as cotações.
        Client::updating(fn () => throw new \RuntimeException('falha injetada'));

        try {
            (new ClientMergeService)->merge($keep, collect([$dup]), true);
            $this->fail('Devia ter lançado.');
        } catch (\RuntimeException) {
        }

        $this->assertSame(1, Proposal::where('client_id', $dup->id)->count());
        $this->assertNotNull(DB::table('clients')->where('id', $dup->id)->first());
    }

    public function test_rejeita_juntar_com_ele_proprio(): void
    {
        $keep = $this->client('Maria');

        $this->expectException(\InvalidArgumentException::class);
        (new ClientMergeService)->merge($keep, collect([$keep]), true);
    }
}
