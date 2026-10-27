<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\ConvertedProposal;
use App\Models\Expense;
use App\Services\PrivateFiles;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Tests\RefreshesDatabaseWithoutMysqlOnlyMigrations;
use Tests\TestCase;

class FicheirosPrivadosTest extends TestCase
{
    use RefreshesDatabaseWithoutMysqlOnlyMigrations;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('local');
        Storage::fake('public');
        Mail::fake();
        Cache::forever('frontend_menus', collect());
        Cache::forever('site_logo', '');
    }

    private function url(string $tipo, string $caminho): string
    {
        return '/gestao/ficheiros/' . $tipo . '/' . $caminho;
    }

    public function test_visitor_cannot_open_private_files(): void
    {
        Storage::disk('local')->put('comissoes/comprovativos/a.pdf', 'pdf');

        $this->get($this->url('comprovativos', 'a.pdf'))->assertRedirect();
    }

    public function test_backoffice_user_receives_the_file_with_safe_headers(): void
    {
        Storage::disk('local')->put('consignment-evaluations/CSG-1/a.png', 'imagem');

        $response = $this->actingAs($this->backofficeUser('admin'))
            ->get($this->url('consignacao', 'CSG-1/a.png'));

        $response->assertOk();
        $this->assertSame('nosniff', $response->headers->get('X-Content-Type-Options'));
        $this->assertStringContainsString('private', $response->headers->get('Cache-Control'));
        $this->assertStringContainsString('inline', $response->headers->get('Content-Disposition'));
    }

    public function test_non_image_files_are_downloaded_instead_of_rendered(): void
    {
        Storage::disk('local')->put('expenses/attachments/x.html', '<script>alert(1)</script>');

        $response = $this->actingAs($this->backofficeUser('admin'))->get($this->url('despesas', 'x.html'));

        $response->assertOk();
        $this->assertStringContainsString('attachment', $response->headers->get('Content-Disposition'));
    }

    public function test_unknown_type_is_not_found(): void
    {
        Storage::disk('local')->put('news/a.png', 'x');

        $this->actingAs($this->backofficeUser('admin'))->get($this->url('news', 'a.png'))->assertNotFound();
    }

    public function test_path_traversal_returns_404(): void
    {
        Storage::disk('local')->put('segredo.txt', 'segredo');
        Storage::disk('local')->put('comissoes/comprovativos/a.pdf', 'pdf');
        $admin = $this->backofficeUser('admin');

        foreach ([
            '../../segredo.txt',
            '..%2f..%2fsegredo.txt',
            '..%2F..%2Fsegredo.txt',
            'x/../../../segredo.txt',
            '%2e%2e/%2e%2e/segredo.txt',
        ] as $caminho) {
            $this->actingAs($admin)->get($this->url('comprovativos', $caminho))->assertNotFound();
        }

        $this->assertFalse(PrivateFiles::caminhoSeguro('../segredo.txt'));
        $this->assertFalse(PrivateFiles::caminhoSeguro('a/../b'));
        $this->assertFalse(PrivateFiles::caminhoSeguro('/etc/passwd'));
        $this->assertFalse(PrivateFiles::caminhoSeguro('a\\b'));
    }

    public function test_old_file_on_public_disk_is_still_served(): void
    {
        Storage::disk('public')->put('movements/attachments/antigo.pdf', 'pdf antigo');

        $this->actingAs($this->backofficeUser('admin'))
            ->get($this->url('movimentos', 'antigo.pdf'))
            ->assertOk();

        $this->assertSame(
            route('admin.v2.files.show', ['tipo' => 'movimentos', 'caminho' => 'antigo.pdf']),
            PrivateFiles::url('movements/attachments/antigo.pdf')
        );
        $this->assertNull(PrivateFiles::url('news/a.png'));
    }

    public function test_private_copy_wins_over_the_public_one(): void
    {
        Storage::disk('public')->put('retoma/RTM-1/a.png', 'publico');
        Storage::disk('local')->put('retoma/RTM-1/a.png', 'privado');

        $this->assertSame('local', PrivateFiles::disco('retoma/RTM-1/a.png'));
    }

    public function test_commission_receipt_upload_is_stored_privately(): void
    {
        $client = Client::create(['name' => 'Maria', 'is_lead' => false, 'lead_status' => 'nova']);
        $converted = ConvertedProposal::forceCreate(['client_id' => $client->id, 'brand' => 'Audi', 'modelCar' => 'A4']);
        $admin = $this->backofficeUser('admin');

        $this->actingAs($admin)
            ->post(route('admin.v2.angariadores.upload-receipt', $converted), [
                'comprovativo' => UploadedFile::fake()->create('recibo.pdf', 50, 'application/pdf'),
            ])->assertRedirect();

        $caminho = $converted->fresh()->comprovativo_pagamento;
        $this->assertStringStartsWith('comissoes/comprovativos/', $caminho);
        Storage::disk('local')->assertExists($caminho);
        $this->assertEmpty(Storage::disk('public')->allFiles());

        $this->actingAs($admin)->get(PrivateFiles::url($caminho))->assertOk();
    }

    public function test_angariador_only_sees_the_receipt_of_their_own_commission(): void
    {
        $angariador = $this->backofficeUser('angariador');
        $outro = $this->backofficeUser('angariador', ['email' => 'outro@example.com']);
        $client = Client::create(['name' => 'Maria', 'is_lead' => false, 'lead_status' => 'nova']);
        Storage::disk('local')->put('comissoes/comprovativos/r.pdf', 'pdf');
        $converted = ConvertedProposal::forceCreate([
            'client_id' => $client->id, 'brand' => 'Audi', 'modelCar' => 'A4',
            'owner_id' => $angariador->id, 'comprovativo_pagamento' => 'comissoes/comprovativos/r.pdf',
        ]);

        $this->actingAs($angariador)->get(route('admin.angariador.comissoes.comprovativo', $converted))->assertOk();
        $this->actingAs($outro)->get(route('admin.angariador.comissoes.comprovativo', $converted))->assertNotFound();
        // Um angariador não navega pela rota genérica de ficheiros.
        $this->actingAs($angariador)->get($this->url('comprovativos', 'r.pdf'))->assertForbidden();
    }

    public function test_movement_attachment_upload_is_stored_privately(): void
    {
        $this->actingAs($this->backofficeUser('admin'))
            ->post(route('admin.v2.movements.store'), [
                'movement_type' => 'expense', 'title' => 'Portagens', 'amount' => 10, 'expense_date' => '2026-10-01',
                'attachment' => UploadedFile::fake()->image('talao.jpg'),
            ])->assertSessionHasNoErrors()->assertRedirect();

        $caminho = Expense::firstOrFail()->attachment_path;
        $this->assertStringStartsWith('movements/attachments/', $caminho);
        Storage::disk('local')->assertExists($caminho);
        $this->assertEmpty(Storage::disk('public')->allFiles());
    }

    public function test_consignment_photos_are_stored_privately_and_attached_to_the_email(): void
    {
        $this->withoutMiddleware();

        $this->post(route('consignment.evaluation.submit'), [
            'brand' => 'BMW', 'model' => '320d', 'year' => 2019, 'kilometers' => 90000, 'plate' => 'AA-00-BB',
            'fuel' => 'Diesel', 'gearbox' => 'Manual', 'condition' => 'Bom',
            // O formulário real envia sempre estes campos (o controlador lê-os sem '??').
            'power' => '', 'displacement' => '', 'price_expectation' => '', 'version' => '', 'color' => '', 'location' => '',
            'name' => 'Rui', 'phone' => '912345678', 'email' => 'rui@example.com', 'privacy_consent' => 1,
            'photos' => [UploadedFile::fake()->image('frente.jpg', 100, 100)],
        ])->assertOk()->assertJson(['status' => 'success']);

        $fotos = \App\Models\ConsignmentEvaluation::firstOrFail()->photos;
        $this->assertCount(1, $fotos);
        $this->assertStringStartsWith('consignment-evaluations/', $fotos[0]);
        Storage::disk('local')->assertExists($fotos[0]);
        $this->assertEmpty(Storage::disk('public')->allFiles());
        $this->assertNotNull(PrivateFiles::caminhoAbsoluto($fotos[0]));
    }

    public function test_command_dry_run_moves_nothing_and_apply_moves_idempotently(): void
    {
        Storage::disk('public')->put('retoma/RTM-1/a.png', 'aaa');
        Storage::disk('public')->put('comissoes/comprovativos/b.pdf', 'bbbb');
        Storage::disk('public')->put('news/publica.png', 'ccc');

        $this->artisan('files:move-to-private')->assertSuccessful();
        Storage::disk('public')->assertExists('retoma/RTM-1/a.png');
        Storage::disk('local')->assertMissing('retoma/RTM-1/a.png');

        $this->artisan('files:move-to-private', ['--apply' => true])->assertSuccessful();
        Storage::disk('local')->assertExists('retoma/RTM-1/a.png');
        Storage::disk('local')->assertExists('comissoes/comprovativos/b.pdf');
        Storage::disk('public')->assertMissing('retoma/RTM-1/a.png');
        Storage::disk('public')->assertMissing('comissoes/comprovativos/b.pdf');
        // Pastas públicas por natureza ficam intactas.
        Storage::disk('public')->assertExists('news/publica.png');
        Storage::disk('local')->assertMissing('news/publica.png');
        $this->assertSame('bbbb', Storage::disk('local')->get('comissoes/comprovativos/b.pdf'));

        // Segunda execução: nada a fazer, nada se perde.
        $this->artisan('files:move-to-private', ['--apply' => true])->assertSuccessful();
        Storage::disk('local')->assertExists('retoma/RTM-1/a.png');
    }

    public function test_command_only_cleans_public_when_private_copy_matches(): void
    {
        Storage::disk('public')->put('expenses/attachments/x.pdf', 'xxxx');
        Storage::disk('local')->put('expenses/attachments/x.pdf', 'xxxx');

        $this->artisan('files:move-to-private', ['--apply' => true])->assertSuccessful();

        Storage::disk('public')->assertMissing('expenses/attachments/x.pdf');
        Storage::disk('local')->assertExists('expenses/attachments/x.pdf');
    }
}
