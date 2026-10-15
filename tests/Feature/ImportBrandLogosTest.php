<?php

namespace Tests\Feature;

use App\Models\Brand;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
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
}
