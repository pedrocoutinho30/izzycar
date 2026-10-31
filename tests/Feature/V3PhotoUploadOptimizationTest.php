<?php

namespace Tests\Feature;

use App\Models\V3Vehicle;
use App\Services\ImageOptimizerService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\RefreshesDatabaseWithoutMysqlOnlyMigrations;
use Tests\TestCase;

class V3PhotoUploadOptimizationTest extends TestCase
{
    use RefreshesDatabaseWithoutMysqlOnlyMigrations;

    /** JPEG ruidoso (difícil de comprimir) para o original ser claramente maior que o otimizado. */
    private function bigJpeg(int $w, int $h): UploadedFile
    {
        $img = imagecreatetruecolor($w, $h);
        mt_srand(7);
        for ($i = 0; $i < 300; $i++) {
            imagefilledrectangle($img, mt_rand(0, $w), mt_rand(0, $h), mt_rand(0, $w), mt_rand(0, $h), imagecolorallocate($img, mt_rand(0, 255), mt_rand(0, 255), mt_rand(0, 255)));
        }
        $tmp = tempnam(sys_get_temp_dir(), 'img') . '.jpg';
        imagejpeg($img, $tmp, 100);

        return new UploadedFile($tmp, 'foto.jpg', 'image/jpeg', null, true);
    }

    public function test_upload_redimensiona_e_comprime_o_original(): void
    {
        Storage::fake('public');
        $vehicle = V3Vehicle::create(['reference' => V3Vehicle::generateReference(), 'brand' => 'BMW', 'model' => 'i4']);
        $file = $this->bigJpeg(3200, 2000);
        $tamanhoOriginal = $file->getSize();

        $this->actingAs($this->backofficeUser('admin'))
            ->post(route('admin.v3.vehicles.photos.upload', $vehicle->id), ['fotos' => [$file]])
            ->assertSessionHas('success');

        $foto = $vehicle->photos()->sole();
        $this->assertTrue($foto->is_cover);
        Storage::disk('public')->assertExists($foto->path);

        $bytes = Storage::disk('public')->get($foto->path);
        [$w, $h] = getimagesizefromstring($bytes);
        fwrite(STDERR, "\n[upload] original {$tamanhoOriginal} B 3200x2000 -> {$foto->path} " . strlen($bytes) . " B {$w}x{$h}\n");

        $this->assertLessThanOrEqual(1920, max($w, $h));
        $this->assertEqualsWithDelta(3200 / 2000, $w / $h, 0.02); // proporção mantida
        $this->assertLessThan($tamanhoOriginal, strlen($bytes));
        $this->assertStringEndsWith(ImageOptimizerService::supportsWebp() ? '.webp' : '.jpg', $foto->path);
    }

    public function test_orientacao_exif_e_aplicada_aos_pixeis(): void
    {
        if (! function_exists('exif_read_data')) {
            $this->markTestSkipped('ext-exif indisponível.');
        }

        // JPEG 300x100 (paisagem) com EXIF Orientation=6 (rodar 90 graus): deve ficar 100x300.
        $img = imagecreatetruecolor(300, 100);
        imagefill($img, 0, 0, imagecolorallocate($img, 200, 30, 30));
        ob_start();
        imagejpeg($img, null, 100);
        $jpeg = ob_get_clean();
        $tiff = "II*\x00\x08\x00\x00\x00" . "\x01\x00" . "\x12\x01\x03\x00\x01\x00\x00\x00\x06\x00\x00\x00" . "\x00\x00\x00\x00";
        $app1 = "\xFF\xE1" . pack('n', 2 + 6 + strlen($tiff)) . "Exif\x00\x00" . $tiff;
        $comExif = substr($jpeg, 0, 2) . $app1 . substr($jpeg, 2);

        Storage::fake('public');
        $file = UploadedFile::fake()->createWithContent('rodada.jpg', $comExif);
        $service = app(ImageOptimizerService::class);
        $path = $service->storeOptimized($file, 'v3-vehicles/9/photos');

        [$w, $h] = getimagesizefromstring(Storage::disk('public')->get($path));
        $this->assertSame([100, 300], [$w, $h]);
    }

    public function test_ficheiro_ilegivel_cai_no_original(): void
    {
        Storage::fake('public');
        $lixo = UploadedFile::fake()->createWithContent('quebrada.jpg', str_repeat('nao sou uma imagem', 50));

        $path = app(ImageOptimizerService::class)->storeOptimized($lixo, 'v3-vehicles/9/photos');

        Storage::disk('public')->assertExists($path);
        $this->assertSame(str_repeat('nao sou uma imagem', 50), Storage::disk('public')->get($path));
    }

    public function test_gif_nao_e_reencodado(): void
    {
        Storage::fake('public');
        $gif = UploadedFile::fake()->image('anim.gif', 50, 50);

        $path = app(ImageOptimizerService::class)->storeOptimized($gif, 'v3-vehicles/9/photos');

        $this->assertStringEndsWith('.gif', $path);
    }

    public function test_imagem_pequena_nao_fica_maior_que_o_original(): void
    {
        Storage::fake('public');
        $png = UploadedFile::fake()->image('p.png', 100, 100);

        $path = app(ImageOptimizerService::class)->storeOptimized($png, 'v3-vehicles/9/photos');

        $this->assertLessThanOrEqual($png->getSize(), Storage::disk('public')->size($path));
    }
}
