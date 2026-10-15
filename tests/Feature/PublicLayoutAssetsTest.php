<?php

namespace Tests\Feature;

use App\Models\Page;
use App\Models\Setting;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Tests\RefreshesDatabaseWithoutMysqlOnlyMigrations;
use Tests\TestCase;

/** Layout público: analytics só após consentimento, JS com defer, menus/logo disponíveis, hero otimizado. */
class PublicLayoutAssetsTest extends TestCase
{
    use RefreshesDatabaseWithoutMysqlOnlyMigrations;

    protected function setUp(): void
    {
        parent::setUp();

        foreach (['address', 'email', 'facebook', 'insta', 'phone', 'vat_number', 'logo'] as $label) {
            Setting::create(['title' => $label, 'label' => $label, 'type' => 'text', 'value' => 'x']);
        }

        DB::connection()->getPdo()->sqliteCreateFunction('FIELD', function ($value, ...$list) {
            $i = array_search($value, $list, true);

            return $i === false ? 0 : $i + 1;
        });
    }

    public function test_layout_has_no_fixed_gtag_and_loads_analytics_after_consent(): void
    {
        $html = $this->get(route('frontend.privacy'))->assertOk()->getContent();

        // Sem gtag.js nem gtag('config') no HTML inicial fora do fluxo do consentimento
        $this->assertSame(0, substr_count($html, '<script async src="https://www.googletagmanager.com/gtag/js'));
        $this->assertSame(1, substr_count($html, "'G-0NT5HLTZ2J'"), 'O ID do GA só deve aparecer uma vez (dentro de loadAnalytics).');

        // loadAnalytics só é invocada dentro dos ramos de consentimento
        $this->assertSame(2, substr_count($html, 'loadAnalytics();'));
        $this->assertStringContainsString('function loadAnalytics()', $html);
        $this->assertDoesNotMatchRegularExpression('/^\s*loadAnalytics\(\);\s*\n\s*\/\/ Verifica/m', $html);
        $this->assertStringContainsString('static.hotjar.com', $html);
    }

    public function test_scripts_are_deferred_and_unused_plugins_removed(): void
    {
        $html = $this->get(route('frontend.privacy'))->assertOk()->getContent();

        foreach (['jquery.min.js', 'bootstrap.bundle.min.js', 'custom.js'] as $file) {
            $this->assertMatchesRegularExpression('#<script src="[^"]*js/' . preg_quote($file, '#') . '" defer></script>#', $html);
        }
        $this->assertStringNotContainsString('jquery.sticky.js', $html);
        $this->assertStringNotContainsString('click-scroll.js', $html);

        // Ordem: jQuery antes de bootstrap e custom.js
        $this->assertLessThan(strpos($html, 'bootstrap.bundle.min.js'), strpos($html, 'jquery.min.js'));
        $this->assertLessThan(strpos($html, 'custom.js'), strpos($html, 'bootstrap.bundle.min.js'));
    }

    public function test_footer_and_layout_use_cached_settings(): void
    {
        $this->get(route('frontend.privacy'))->assertOk();

        DB::flushQueryLog();
        DB::enableQueryLog();
        $this->get(route('frontend.terms'))->assertOk();
        $settingsQueries = collect(DB::getQueryLog())->filter(fn ($q) => str_contains($q['query'], 'from "settings"'));
        DB::disableQueryLog();

        $this->assertCount(0, $settingsQueries, $settingsQueries->pluck('query')->implode(' | '));
    }

    public function test_menus_and_logo_are_available_on_public_pages(): void
    {
        Cache::forget('site_logo');
        Cache::forget('frontend_menus');

        $this->get(route('frontend.terms'))
            ->assertOk()
            ->assertSee('storage/x', false);
    }

    public function test_homepage_preloads_optimised_hero(): void
    {
        $type = \App\Models\PageType::create(['name' => 'Home']);
        Page::create(['title' => 'Home', 'slug' => 'homepage', 'page_type_id' => $type->id]);

        $html = $this->get(route('frontend.home'))->assertOk()->getContent();

        $this->assertStringContainsString('rel="preload" as="image"', $html);
        $this->assertStringContainsString('hero-640.webp', $html);
        $this->assertStringContainsString('hero-1024.webp', $html);
        $this->assertStringNotContainsString('img/hero.webp', $html);
        $this->assertStringNotContainsString('img/2.jpg', $html);
        foreach (['hero-640.webp', 'hero-1024.webp', '1-900.jpg', '2-800.jpg', '3-900.jpg'] as $f) {
            $this->assertFileExists(public_path('img/' . $f));
        }
        $this->assertLessThan(160 * 1024, filesize(public_path('img/hero-1024.webp')));
    }

    public function test_main_public_pages_render_with_the_deferred_layout(): void
    {
        foreach (['vehicles.list', 'frontend.news'] as $name) {
            $this->get(route($name))->assertOk()->assertSee('js/jquery.min.js" defer', false);
        }
    }
}
