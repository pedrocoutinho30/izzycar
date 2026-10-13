<?php

namespace Tests\Feature;

use App\Models\V3Vehicle;
use App\Models\V3VehiclePhoto;
use App\Support\VehicleCache;
use Illuminate\Support\Facades\Cache;
use Tests\RefreshesDatabaseWithoutMysqlOnlyMigrations;
use Tests\TestCase;

class VehicleCacheTest extends TestCase
{
    use RefreshesDatabaseWithoutMysqlOnlyMigrations;

    public function test_keys_change_when_cache_is_flushed(): void
    {
        $before = VehicleCache::key('vehicles_abc');

        VehicleCache::flush();

        $this->assertNotSame($before, VehicleCache::key('vehicles_abc'));
        $this->assertStringStartsWith('vehicles_abc_v', VehicleCache::key('vehicles_abc'));
    }

    public function test_saving_a_vehicle_or_its_photo_invalidates_the_public_cache(): void
    {
        $key = VehicleCache::key('filtered_vehicles_x');

        $vehicle = V3Vehicle::create(['reference' => V3Vehicle::generateReference(), 'brand' => 'BMW', 'model' => 'i4', 'status' => 'em_stock']);
        $afterCreate = VehicleCache::key('filtered_vehicles_x');
        $this->assertNotSame($key, $afterCreate);

        $vehicle->update(['status' => 'vendido']);
        $afterUpdate = VehicleCache::key('filtered_vehicles_x');
        $this->assertNotSame($afterCreate, $afterUpdate);

        V3VehiclePhoto::create(['v3_vehicle_id' => $vehicle->id, 'path' => 'x.jpg', 'order_position' => 0, 'is_cover' => true]);
        $afterPhoto = VehicleCache::key('filtered_vehicles_x');
        $this->assertNotSame($afterUpdate, $afterPhoto);

        $vehicle->delete();
        $this->assertNotSame($afterPhoto, VehicleCache::key('filtered_vehicles_x'));
    }

    public function test_listing_reflects_a_status_change_without_clearing_the_cache_by_hand(): void
    {
        Cache::forever('frontend_menus', collect());
        Cache::forever('site_logo', '');
        $vehicle = V3Vehicle::create(['reference' => V3Vehicle::generateReference(), 'brand' => 'BMW', 'model' => 'i4', 'status' => 'em_stock', 'show_online' => true]);

        $first = Cache::remember(VehicleCache::key('probe'), 600, fn () => 'antigo');
        $vehicle->update(['status' => 'vendido']);

        $this->assertSame('antigo', $first);
        $this->assertSame('novo', Cache::remember(VehicleCache::key('probe'), 600, fn () => 'novo'));
    }
}
