<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\Proposal;
use App\Models\Setting;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Tests\RefreshesDatabaseWithoutMysqlOnlyMigrations;
use Tests\TestCase;

/** Pré-visualização do link da cotação (WhatsApp/Facebook): og:image em JPEG. */
class CotacaoPreviewWhatsappTest extends TestCase
{
    use RefreshesDatabaseWithoutMysqlOnlyMigrations;

    protected function setUp(): void
    {
        parent::setUp();
        Cache::forever('frontend_menus', collect());
        Cache::forever('site_logo', '');
        Storage::fake('public');
        foreach (['email' => 'geral@izzycar.pt', 'phone' => '928459346', 'facebook' => 'x', 'insta' => 'x'] as $label => $value) {
            Setting::create(['title' => $label, 'label' => $label, 'type' => 'text', 'value' => $value]);
        }
    }

    private function proposal(array $extra = []): Proposal
    {
        $client = Client::create(['name' => 'Maria Silva', 'email' => 'maria@example.com', 'phone' => '912345678', 'is_lead' => true, 'lead_status' => 'nova']);

        return Proposal::create($extra + [
            'client_id' => $client->id, 'brand' => 'BMW', 'model' => 'i4', 'transport_cost' => 1350,
            'commission_cost' => 615, 'proposed_car_value' => 30000, 'proposal_code' => 'BIC12345', 'status' => 'Pendente',
        ]);
    }

    private function guardarFoto(string $caminhoWebp): void
    {
        $im = imagecreatetruecolor(1600, 900);
        imagefill($im, 0, 0, imagecolorallocate($im, 200, 30, 30));
        ob_start();
        imagepng($im);
        Storage::disk('public')->put($caminhoWebp, ob_get_clean()); // Intervention lê pelo conteúdo
    }

    public function test_pagina_da_cotacao_tem_og_image_em_jpeg(): void
    {
        $this->proposal(['images' => 'proposals/1/1/abc_large.avif']);

        $this->get(route('proposals.detail', 'BIC12345'))->assertOk()
            ->assertSee('property="og:image" content="' . route('proposals.preview-image', 'BIC12345') . '"', false)
            ->assertSee('property="og:image:type" content="image/jpeg"', false)
            ->assertSee('name="twitter:card" content="summary_large_image"', false);
    }

    public function test_cotacao_sem_foto_usa_o_logotipo(): void
    {
        $this->proposal();

        $this->get(route('proposals.detail', 'BIC12345'))->assertOk()
            ->assertSee('property="og:image" content="' . asset('img/logo-simples.png') . '"', false)
            ->assertDontSee('foto.jpg', false);
    }

    public function test_rota_da_foto_devolve_jpeg_1200x630(): void
    {
        $this->proposal(['images' => 'proposals/1/1/abc_large.avif']);
        $this->guardarFoto('proposals/1/1/abc_large.webp');

        $r = $this->get(route('proposals.preview-image', 'BIC12345'))->assertOk()->assertHeader('Content-Type', 'image/jpeg');

        [$w, $h, $tipo] = getimagesizefromstring($r->getContent());
        $this->assertSame([1200, 630, IMAGETYPE_JPEG], [$w, $h, $tipo]);
    }

    public function test_foto_inexistente_ou_codigo_errado_dao_404(): void
    {
        $this->proposal(['images' => 'proposals/1/1/em-falta_large.avif']);

        $this->get(route('proposals.preview-image', 'BIC12345'))->assertNotFound();
        $this->get(route('proposals.preview-image', 'NAOEXISTE'))->assertNotFound();
    }
}
