<?php

namespace Tests\Feature;

use App\Models\Brand;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Tests\RefreshesDatabaseWithoutMysqlOnlyMigrations;
use Tests\TestCase;

class BrandLogoAdminTest extends TestCase
{
    use RefreshesDatabaseWithoutMysqlOnlyMigrations;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('public');
        Cache::forever('frontend_menus', collect());
        Cache::forever('site_logo', '');
    }

    public function test_list_shows_brands_and_filters_those_without_logo(): void
    {
        Brand::create(['name' => 'Audi', 'logo_path' => 'brands/audi.png']);
        Brand::create(['name' => 'Matra']);

        $admin = $this->backofficeUser('admin');

        $this->actingAs($admin)->get(route('admin.v2.brands.index'))
            ->assertOk()->assertSee('Logo Audi')->assertSee('Matra');

        $this->actingAs($admin)->get(route('admin.v2.brands.index', ['sem_logo' => 1]))
            ->assertOk()->assertSee('Matra')->assertDontSee('Logo Audi');
    }

    public function test_logo_can_be_added_replaced_and_removed(): void
    {
        $brand = Brand::create(['name' => 'Matra']);
        $admin = $this->backofficeUser('admin');

        $this->actingAs($admin)->post(route('admin.v2.brands.logo.update', $brand), ['logo' => UploadedFile::fake()->image('a.png')])
            ->assertSessionHasNoErrors();
        $first = $brand->fresh()->logo_path;
        Storage::disk('public')->assertExists($first);

        $this->actingAs($admin)->post(route('admin.v2.brands.logo.update', $brand), ['logo' => UploadedFile::fake()->image('b.png')]);
        $second = $brand->fresh()->logo_path;
        $this->assertNotSame($first, $second);
        Storage::disk('public')->assertMissing($first);

        $this->actingAs($admin)->delete(route('admin.v2.brands.logo.destroy', $brand));
        $this->assertNull($brand->fresh()->logo_path);
        Storage::disk('public')->assertMissing($second);
    }

    public function test_non_images_are_rejected(): void
    {
        $brand = Brand::create(['name' => 'Matra']);

        $this->actingAs($this->backofficeUser('admin'))
            ->post(route('admin.v2.brands.logo.update', $brand), ['logo' => UploadedFile::fake()->create('x.svg', 5, 'image/svg+xml')])
            ->assertSessionHasErrors('logo');
        $this->assertNull($brand->fresh()->logo_path);
    }

    public function test_shared_file_is_kept_when_another_brand_uses_it(): void
    {
        Storage::disk('public')->put('brands/volkswagen.png', 'x');
        $vw = Brand::create(['name' => 'VW', 'logo_path' => 'brands/volkswagen.png']);
        $other = Brand::create(['name' => 'Volkswagen', 'logo_path' => 'brands/volkswagen.png']);
        $admin = $this->backofficeUser('admin');

        $this->actingAs($admin)->post(route('admin.v2.brands.logo.update', $vw), ['logo' => UploadedFile::fake()->image('a.png')]);
        $this->assertNotSame('brands/volkswagen.png', $vw->fresh()->logo_path);
        Storage::disk('public')->assertExists('brands/volkswagen.png');

        $this->actingAs($admin)->delete(route('admin.v2.brands.logo.destroy', $other));
        $this->assertNull($other->fresh()->logo_path);
        // Já ninguém usa o ficheiro: este remove-se.
        Storage::disk('public')->assertMissing('brands/volkswagen.png');

        $this->actingAs($admin)->post(route('admin.v2.brands.logo.update', $other), ['logo' => UploadedFile::fake()->image('b.png')]);
        $shared = $other->fresh()->logo_path;
        $vw->update(['logo_path' => $shared]);
        $this->actingAs($admin)->delete(route('admin.v2.brands.logo.destroy', $other));
        Storage::disk('public')->assertExists($shared);
    }
}
