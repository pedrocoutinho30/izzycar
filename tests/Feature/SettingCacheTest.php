<?php

namespace Tests\Feature;

use App\Models\Setting;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Tests\RefreshesDatabaseWithoutMysqlOnlyMigrations;
use Tests\TestCase;

class SettingCacheTest extends TestCase
{
    use RefreshesDatabaseWithoutMysqlOnlyMigrations;

    private function queryCount(callable $fn): int
    {
        DB::flushQueryLog();
        DB::enableQueryLog();
        $fn();
        $n = count(DB::getQueryLog());
        DB::disableQueryLog();

        return $n;
    }

    public function test_cached_reads_the_database_only_once(): void
    {
        Setting::create(['title' => 'Telefone', 'label' => 'phone', 'type' => 'text', 'value' => '123']);

        $first = $this->queryCount(fn () => Setting::cached());
        $second = $this->queryCount(fn () => Setting::cached());

        $this->assertSame(1, $first);
        $this->assertSame(0, $second);
        $this->assertSame('123', Setting::cachedValue('phone'));
        $this->assertSame('x', Setting::cachedValue('inexistente', 'x'));
    }

    public function test_saving_and_deleting_a_setting_invalidate_the_cache(): void
    {
        $setting = Setting::create(['title' => 'Telefone', 'label' => 'phone', 'type' => 'text', 'value' => '123']);
        $this->assertSame('123', Setting::cachedValue('phone'));

        $setting->update(['value' => '456']);
        $this->assertFalse(Cache::has(Setting::CACHE_KEY));
        $this->assertSame('456', Setting::cachedValue('phone'));

        Setting::create(['title' => 'Email', 'label' => 'email', 'type' => 'text', 'value' => 'a@b.pt']);
        $this->assertSame('a@b.pt', Setting::cachedValue('email'));

        $setting->delete();
        $this->assertNull(Setting::cachedValue('phone'));
    }

    public function test_backoffice_update_refreshes_cache_and_logo(): void
    {
        $this->withoutMiddleware();
        $setting = Setting::create(['title' => 'Telefone', 'label' => 'phone', 'type' => 'text', 'value' => '123']);
        Setting::cached();
        Cache::forever('site_logo', 'antigo.png');

        $this->put(route('admin.v2.settings.update', $setting->id), [
            'titulo' => 'Telefone', 'label' => 'phone', 'tipo' => 'text', 'valor' => '999',
        ])->assertRedirect();

        $this->assertSame('999', Setting::cachedValue('phone'));
        $this->assertFalse(Cache::has('site_logo'));
    }
}
