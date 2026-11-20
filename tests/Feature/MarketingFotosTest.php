<?php

namespace Tests\Feature;

use App\Models\V3Vehicle;
use App\Models\V3VehiclePhoto;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Tests\RefreshesDatabaseWithoutMysqlOnlyMigrations;
use Tests\TestCase;

/** Rota autenticada que serve as fotos das viaturas ao editor (mesmo domínio, redimensionadas). */
class MarketingFotosTest extends TestCase
{
    use RefreshesDatabaseWithoutMysqlOnlyMigrations;

    protected function setUp(): void
    {
        parent::setUp();
        Cache::forever('frontend_menus', collect());
        Cache::forever('site_logo', '');
        Storage::fake('public');
    }

    private function foto(string $path = 'v3-vehicles/1/photos/a.jpg', int $w = 3000, int $h = 2000): V3VehiclePhoto
    {
        $img = imagecreatetruecolor($w, $h);
        ob_start();
        imagejpeg($img);
        Storage::disk('public')->put($path, ob_get_clean());
        $v = V3Vehicle::create(['reference' => V3Vehicle::generateReference(), 'brand' => 'BMW', 'model' => 'i4']);

        return V3VehiclePhoto::create(['v3_vehicle_id' => $v->id, 'path' => $path, 'order_position' => 1]);
    }

    public function test_devolve_imagem_redimensionada(): void
    {
        $photo = $this->foto();

        $res = $this->actingAs($this->backofficeUser('admin'))->get(route('admin.marketing.photos.show', $photo));

        $res->assertOk();
        $this->assertSame('image/jpeg', $res->headers->get('Content-Type'));
        [$w, $h] = getimagesizefromstring($res->getContent());
        $this->assertSame(2160, $w);
        $this->assertSame(1440, $h);
    }

    public function test_nao_amplia_fotos_pequenas(): void
    {
        $photo = $this->foto('v3-vehicles/1/photos/b.jpg', 800, 600);

        $res = $this->actingAs($this->backofficeUser('admin'))->get(route('admin.marketing.photos.show', $photo));

        [$w] = getimagesizefromstring($res->getContent());
        $this->assertSame(800, $w);
    }

    public function test_ficheiro_em_falta_da_404(): void
    {
        $photo = $this->foto();
        Storage::disk('public')->delete($photo->path);

        $this->actingAs($this->backofficeUser('admin'))
            ->get(route('admin.marketing.photos.show', $photo))->assertNotFound();
    }

    public function test_sem_sessao_nao_acede(): void
    {
        $photo = $this->foto();

        $this->get(route('admin.marketing.photos.show', $photo))->assertRedirect();
    }

    public function test_angariador_nao_acede(): void
    {
        $photo = $this->foto();

        $this->actingAs($this->backofficeUser('angariador'))
            ->get(route('admin.marketing.photos.show', $photo))->assertForbidden();
    }
}
