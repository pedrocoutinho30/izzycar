<?php

namespace Tests\Feature;

use App\Models\Brand;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Client\ConnectionException;
use Tests\RefreshesDatabaseWithoutMysqlOnlyMigrations;
use Tests\TestCase;

class ImportBrandLogosTest extends TestCase
{
    use RefreshesDatabaseWithoutMysqlOnlyMigrations;

    private function dataset(): string
    {
        $path = tempnam(sys_get_temp_dir(), 'logos');
        $item = fn ($name, $slug) => ['name' => $name, 'slug' => $slug, 'image' => ['thumb' => "https://example.test/thumb/$slug.png"]];
        file_put_contents($path, json_encode([$item('Citroën', 'citroen'), $item('Volkswagen', 'volkswagen'), $item('Audi', 'audi')]));

        return $path;
    }

    public function test_logos_are_downloaded_and_matched_by_name_and_alias(): void
    {
        Storage::fake('public');
        Http::fake(['example.test/*' => Http::response('png', 200, ['Content-Type' => 'image/png'])]);
        Brand::create(['name' => 'Citroen']);
        Brand::create(['name' => 'VW']);
        Brand::create(['name' => 'Matra']);

        $this->artisan('brands:import-logos', ['file' => $this->dataset()])->assertSuccessful();

        $this->assertSame('brands/citroen.png', Brand::where('name', 'Citroen')->value('logo_path'));
        $this->assertSame('brands/volkswagen.png', Brand::where('name', 'VW')->value('logo_path'));
        $this->assertNull(Brand::where('name', 'Matra')->value('logo_path'));
        Storage::disk('public')->assertExists('brands/citroen.png');
    }

    public function test_dry_run_and_existing_logos_are_left_untouched(): void
    {
        Storage::fake('public');
        Http::fake();
        Brand::create(['name' => 'Audi']);
        Brand::create(['name' => 'Citroen', 'logo_path' => 'brands/custom.png']);

        $this->artisan('brands:import-logos', ['file' => $this->dataset(), '--dry-run' => true])->assertSuccessful();
        Http::assertNothingSent();
        $this->assertNull(Brand::where('name', 'Audi')->value('logo_path'));

        Http::fake(['example.test/*' => Http::response('png', 200, ['Content-Type' => 'image/png'])]);
        $this->artisan('brands:import-logos', ['file' => $this->dataset()])->assertSuccessful();
        $this->assertSame('brands/custom.png', Brand::where('name', 'Citroen')->value('logo_path'));
    }

    public function test_failed_download_does_not_set_a_logo(): void
    {
        Storage::fake('public');
        Http::fake(['example.test/*' => Http::response('', 404)]);
        Brand::create(['name' => 'Audi']);

        $this->artisan('brands:import-logos', ['file' => $this->dataset()])->assertSuccessful();

        $this->assertNull(Brand::where('name', 'Audi')->value('logo_path'));
    }

    private function fakeImage(): void
    {
        Http::fake(['example.test/*' => Http::response('png', 200, ['Content-Type' => 'image/png'])]);
    }

    public function test_force_removes_old_file_unless_another_brand_uses_it(): void
    {
        Storage::fake('public');
        $this->fakeImage();
        Storage::disk('public')->put('brands/manual.png', 'x');
        Storage::disk('public')->put('brands/shared.png', 'x');
        Brand::create(['name' => 'Audi', 'logo_path' => 'brands/manual.png']);
        Brand::create(['name' => 'Citroen', 'logo_path' => 'brands/shared.png']);
        Brand::create(['name' => 'Outra', 'logo_path' => 'brands/shared.png']);

        $this->artisan('brands:import-logos', ['file' => $this->dataset(), '--force' => true])->assertSuccessful();

        $this->assertSame('brands/audi.png', Brand::where('name', 'Audi')->value('logo_path'));
        Storage::disk('public')->assertMissing('brands/manual.png');
        Storage::disk('public')->assertExists('brands/shared.png');
    }

    public function test_command_continues_after_connection_exception_and_incomplete_items(): void
    {
        Storage::fake('public');
        Http::fake([
            'example.test/thumb/audi.png' => fn () => throw new ConnectionException('timeout'),
            'example.test/*' => Http::response('png', 200, ['Content-Type' => 'image/png']),
        ]);
        $path = tempnam(sys_get_temp_dir(), 'logos');
        file_put_contents($path, json_encode([
            ['name' => 'Audi', 'slug' => 'audi', 'image' => ['thumb' => 'https://example.test/thumb/audi.png']],
            ['name' => 'Volkswagen', 'slug' => 'volkswagen', 'image' => []],
            ['slug' => 'sem-nome'],
            ['name' => 'Citroën', 'slug' => 'citroen', 'image' => ['thumb' => 'https://example.test/thumb/citroen.png']],
        ]));
        Brand::create(['name' => 'Audi']);
        Brand::create(['name' => 'Volkswagen']);
        Brand::create(['name' => 'Citroen']);

        $this->artisan('brands:import-logos', ['file' => $path])->assertSuccessful();

        $this->assertNull(Brand::where('name', 'Audi')->value('logo_path'));
        $this->assertNull(Brand::where('name', 'Volkswagen')->value('logo_path'));
        $this->assertSame('brands/citroen.png', Brand::where('name', 'Citroen')->value('logo_path'));
    }

    public function test_non_https_url_and_oversized_body_count_as_failures(): void
    {
        Storage::fake('public');
        Http::fake(['example.test/*' => Http::response(str_repeat('a', 2 * 1024 * 1024), 200, ['Content-Type' => 'image/png'])]);
        $path = tempnam(sys_get_temp_dir(), 'logos');
        file_put_contents($path, json_encode([
            ['name' => 'Audi', 'slug' => 'audi', 'image' => ['thumb' => 'http://example.test/thumb/audi.png']],
            ['name' => 'Citroën', 'slug' => 'citroen', 'image' => ['thumb' => 'https://example.test/thumb/citroen.png']],
        ]));
        Brand::create(['name' => 'Audi']);
        Brand::create(['name' => 'Citroen']);

        $this->artisan('brands:import-logos', ['file' => $path])->assertSuccessful();

        $this->assertNull(Brand::where('name', 'Audi')->value('logo_path'));
        $this->assertNull(Brand::where('name', 'Citroen')->value('logo_path'));
        Storage::disk('public')->assertMissing('brands/citroen.png');
    }
}
