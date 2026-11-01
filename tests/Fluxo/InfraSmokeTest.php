<?php

namespace Tests\Fluxo;

use App\Models\Client;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use RuntimeException;
use Tests\Fluxo\Concerns\UsaMysqlIsolado;
use Tests\TestCase;

class InfraSmokeTest extends TestCase
{
    use UsaMysqlIsolado;

    public function test_ligacao_a_base_certa(): void
    {
        $this->assertSame('mysql', DB::connection()->getDriverName());
        $this->assertSame('izzycar_fluxo_teste', DB::selectOne('SELECT DATABASE() AS db')->db);
        fwrite(STDERR, "\n[fluxo] MySQL ".DB::selectOne('SELECT VERSION() AS v')->v."\n");
    }

    public function test_migracoes_aplicadas(): void
    {
        $ficheiros = count(glob(database_path('migrations/*.php')));
        $aplicadas = DB::table('migrations')->count();
        fwrite(STDERR, "\n[fluxo] migrations: {$aplicadas}/{$ficheiros}; falhadas: ".count(self::$migrationsFalhadas)."\n");
        $this->assertGreaterThan(0, $aplicadas);

        // Achado: estas migrations falham num migrate:fresh em MySQL 8.0.33 (ver README).
        // O teste fixa o conjunto conhecido: uma falha nova (ou corrigida) obriga a rever.
        $conhecidas = [
            '2026_01_12_094536_add_consignment_page.php',
            '2026_05_27_000001_create_vehicle_inspection_tables.php',
            '2026_05_27_000002_add_generated_from_inspection_id_to_v3_vehicles.php',
            '2026_08_05_110100_add_angariador_code_to_clients_and_form_proposals.php',
            '2026_08_05_150000_add_comprovativo_pagamento_to_converted_proposals_table.php',
        ];
        $falhadas = array_keys(self::$migrationsFalhadas);
        sort($falhadas);
        $this->assertSame($conhecidas, $falhadas, 'Conjunto de migrations falhadas mudou: '.json_encode(self::$migrationsFalhadas));
        $this->assertSame($ficheiros, $aplicadas + count($falhadas));
    }

    public function test_criar_e_ler_user_e_client(): void
    {
        $user = User::factory()->create(['last_name' => 'Teste', 'password' => 'secret']);
        $client = Client::create(['name' => 'Cliente Fluxo', 'email' => 'fluxo@example.test', 'owner_id' => $user->id]);

        $this->assertSame($user->email, User::find($user->id)->email);
        $this->assertSame('Cliente Fluxo', Client::find($client->id)->name);
    }

    public function test_limpeza_entre_testes_deixa_tabelas_vazias(): void
    {
        $this->assertSame(0, DB::table('users')->count());
        $this->assertSame(0, DB::table('clients')->count());
    }

    public function test_mail_em_modo_array(): void
    {
        $this->assertSame('array', config('mail.default'));
        $this->assertSame('sync', config('queue.default'));
    }

    public function test_guarda_aborta_com_outra_base(): void
    {
        $ok = ['driver' => 'mysql', 'host' => '127.0.0.1', 'database' => 'izzycar_fluxo_teste'];
        self::garantirBaseIsolada($ok, 'izzycar_fluxo_teste');

        foreach ([
            [array_merge($ok, ['database' => 'gestaoSoft']), 'gestaoSoft'],
            [array_merge($ok, ['host' => 'srv.exemplo.com']), 'izzycar_fluxo_teste'],
            [array_merge($ok, ['driver' => 'sqlite']), 'izzycar_fluxo_teste'],
            [$ok, 'outra'],
        ] as [$cfg, $db]) {
            try {
                self::garantirBaseIsolada($cfg, $db);
                $this->fail('A guarda devia abortar.');
            } catch (RuntimeException $e) {
                $this->assertStringContainsString('ABORTADO', $e->getMessage());
            }
        }
    }
}
