<?php

namespace Tests\Feature;

use App\Models\MarketingPost;
use App\Models\User;
use App\Models\V3Vehicle;
use App\Models\V3VehiclePhoto;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Tests\RefreshesDatabaseWithoutMysqlOnlyMigrations;
use Tests\TestCase;

/** Biblioteca / histórico de publicações. */
class MarketingBibliotecaTest extends TestCase
{
    use RefreshesDatabaseWithoutMysqlOnlyMigrations;

    protected function setUp(): void
    {
        parent::setUp();
        Cache::forever('frontend_menus', collect());
        Cache::forever('site_logo', '');
    }

    private function criar(array $extra = []): MarketingPost
    {
        return MarketingPost::create($extra + [
            'type' => 'veiculo', 'template' => 'premium', 'format' => 'feed_square', 'status' => 'rascunho',
            'title' => 'Post base', 'data' => [], 'config' => [],
        ]);
    }

    public function test_lista_mostra_autor_template_formato_e_estado(): void
    {
        $autor = User::factory()->create(['name' => 'Marta', 'last_name' => 'Silva', 'password' => 'secret']);
        $this->criar(['title' => 'Audi A3 azul', 'created_by' => $autor->id]);

        $this->actingAs($this->backofficeUser('admin'))->get(route('admin.marketing.index'))
            ->assertOk()->assertSee('Audi A3 azul')->assertSee('Marta')->assertSee('Premium')->assertSee('Rascunho')->assertSee('1080×1080');
    }

    public function test_filtra_por_estado_e_tipo_e_pesquisa(): void
    {
        $this->criar(['title' => 'Rascunho veículo']);
        $this->criar(['title' => 'Aprovado veículo', 'status' => 'aprovado']);
        $this->criar(['title' => 'Recomendação BMW', 'type' => 'recomendacao', 'template' => 'recomendacao']);
        $admin = $this->backofficeUser('admin');

        $this->actingAs($admin)->get(route('admin.marketing.index', ['status' => 'aprovado']))
            ->assertSee('Aprovado veículo')->assertDontSee('Rascunho veículo');
        $this->actingAs($admin)->get(route('admin.marketing.index', ['type' => 'recomendacao']))
            ->assertSee('Recomendação BMW')->assertDontSee('Aprovado veículo');
        $this->actingAs($admin)->get(route('admin.marketing.index', ['q' => 'BMW']))
            ->assertSee('Recomendação BMW')->assertDontSee('Rascunho veículo');
    }

    public function test_indice_tem_atalhos_de_criacao_e_importacao(): void
    {
        $this->actingAs($this->backofficeUser('admin'))->get(route('admin.marketing.index'))
            ->assertSee(route('admin.marketing.create'), false)
            ->assertSee(route('admin.marketing.vehicles.create'), false)
            ->assertSee(route('admin.marketing.recommendations.create'), false)
            ->assertSee(route('admin.marketing.import-recommendations'), false);
    }

    public function test_miniatura_da_foto_respeita_o_parametro_w(): void
    {
        Storage::fake('public');
        $img = imagecreatetruecolor(3000, 2000);
        ob_start(); imagejpeg($img); Storage::disk('public')->put('v3-vehicles/1/photos/a.jpg', ob_get_clean());
        $v = V3Vehicle::create(['reference' => V3Vehicle::generateReference(), 'brand' => 'BMW', 'model' => 'i4']);
        $photo = V3VehiclePhoto::create(['v3_vehicle_id' => $v->id, 'path' => 'v3-vehicles/1/photos/a.jpg', 'order_position' => 1]);

        $res = $this->actingAs($this->backofficeUser('admin'))->get(route('admin.marketing.photos.show', [$photo, 'w' => 400]));

        [$w] = getimagesizefromstring($res->getContent());
        $this->assertSame(400, $w);
    }

    public function test_w_absurdo_e_limitado(): void
    {
        Storage::fake('public');
        $img = imagecreatetruecolor(3000, 2000);
        ob_start(); imagejpeg($img); Storage::disk('public')->put('v3-vehicles/1/photos/a.jpg', ob_get_clean());
        $v = V3Vehicle::create(['reference' => V3Vehicle::generateReference(), 'brand' => 'BMW', 'model' => 'i4']);
        $photo = V3VehiclePhoto::create(['v3_vehicle_id' => $v->id, 'path' => 'v3-vehicles/1/photos/a.jpg', 'order_position' => 1]);

        $res = $this->actingAs($this->backofficeUser('admin'))->get(route('admin.marketing.photos.show', [$photo, 'w' => 99999]));

        [$w] = getimagesizefromstring($res->getContent());
        $this->assertSame(2160, $w);
    }
}
