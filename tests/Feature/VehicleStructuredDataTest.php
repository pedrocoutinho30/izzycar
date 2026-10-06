<?php

namespace Tests\Feature;

use App\Models\Setting;
use App\Models\V3Vehicle;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\RefreshesDatabaseWithoutMysqlOnlyMigrations;
use Tests\TestCase;

class VehicleStructuredDataTest extends TestCase
{
    use RefreshesDatabaseWithoutMysqlOnlyMigrations;

    protected function setUp(): void
    {
        parent::setUp();

        Cache::forever('frontend_menus', collect());
        Cache::forever('site_logo', '');

        foreach (['address', 'email', 'facebook', 'insta', 'phone', 'vat_number'] as $label) {
            Setting::create(['title' => $label, 'label' => $label, 'type' => 'text', 'value' => 'x']);
        }

        // O controlador usa FIELD() (MySQL); emulamo-lo no SQLite dos testes.
        DB::connection()->getPdo()->sqliteCreateFunction('FIELD', function ($value, ...$list) {
            $i = array_search($value, $list, true);

            return $i === false ? 0 : $i + 1;
        });
    }

    /** @return array<int, array<string, mixed>> */
    private function jsonLd(array $attributes): array
    {
        $vehicle = V3Vehicle::create($attributes + [
            'reference' => V3Vehicle::generateReference(),
            'brand' => 'Porsche',
            'model' => 'Taycan',
            'show_online' => true,
        ]);

        $html = $this->get(route('vehicles.details', ['brand' => 'porsche', 'model' => 'taycan', 'id' => $vehicle->reference]))
            ->assertOk()
            ->getContent();

        preg_match_all('#<script type="application/ld\+json">(.*?)</script>#s', $html, $matches);

        return array_map(fn ($json) => json_decode($json, true, 512, JSON_THROW_ON_ERROR), $matches[1]);
    }

    private function types(array $blocks): array
    {
        return array_column($blocks, '@type');
    }

    public function test_available_vehicle_with_price_has_car_with_offer(): void
    {
        $blocks = $this->jsonLd(['status' => 'em_stock', 'asking_price' => 89990.4]);

        $car = collect($blocks)->firstWhere('@type', 'Car');
        $this->assertNotNull($car);
        $this->assertSame('Offer', $car['offers']['@type']);
        $this->assertSame('89990', $car['offers']['price']);
        $this->assertSame('EUR', $car['offers']['priceCurrency']);
        $this->assertSame('https://schema.org/UsedCondition', $car['offers']['itemCondition']);
        $this->assertSame('https://schema.org/InStock', $car['offers']['availability']);
        $this->assertSame($car['url'], $car['offers']['url']);
        $this->assertStringStartsWith('http', $car['offers']['url']);
        $this->assertSame('https://izzycar.pt/#autodealer', $car['offers']['seller']['@id']);
        $this->assertContains('BreadcrumbList', $this->types($blocks));
    }

    #[DataProvider('noProductCases')]
    public function test_vehicle_without_public_price_has_no_car_block(array $attributes): void
    {
        $blocks = $this->jsonLd($attributes);

        $this->assertNotContains('Car', $this->types($blocks));
        $this->assertContains('BreadcrumbList', $this->types($blocks));
    }

    public static function noProductCases(): array
    {
        return [
            'vendida com preço' => [['status' => 'vendido', 'asking_price' => 50000]],
            'vendida sem preço' => [['status' => 'vendido', 'asking_price' => null]],
            'reservada' => [['status' => 'reservado', 'asking_price' => 50000]],
            'à venda sem preço' => [['status' => 'em_stock', 'asking_price' => null]],
            'à venda com preço zero' => [['status' => 'em_stock', 'asking_price' => 0]],
        ];
    }
}
