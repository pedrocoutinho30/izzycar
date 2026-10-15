<?php

namespace Tests\Feature;

use App\Models\Page;
use App\Models\PageType;
use App\Models\Partner;
use App\Models\Setting;
use App\Models\V3Vehicle;
use App\Services\ThumbnailService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Tests\RefreshesDatabaseWithoutMysqlOnlyMigrations;
use Tests\TestCase;

/** Fase 2: telefone da home, títulos, miniaturas (logo/parceiros), font-display e CSS do Swiper condicional. */
class PublicPerformancePhase2Test extends TestCase
{
    use RefreshesDatabaseWithoutMysqlOnlyMigrations;

    private const SWIPER_CSS = 'swiper@9/swiper-bundle.min.css';

    protected function setUp(): void
    {
        parent::setUp();

        Cache::flush();
        foreach (['address', 'email', 'facebook', 'insta', 'phone', 'vat_number'] as $label) {
            Setting::create(['title' => $label, 'label' => $label, 'type' => 'text', 'value' => '+351928459346']);
        }
        Setting::create(['title' => 'logo', 'label' => 'logo', 'type' => 'image', 'value' => 'settings/logo.png']);

        DB::connection()->getPdo()->sqliteCreateFunction('FIELD', function ($value, ...$list) {
            $i = array_search($value, $list, true);

            return $i === false ? 0 : $i + 1;
        });
    }

    private function home(): string
    {
        $type = PageType::create(['name' => 'Home']);
        Page::create(['title' => 'Home', 'slug' => 'homepage', 'page_type_id' => $type->id]);

        return $this->get(route('frontend.home'))->assertOk()->getContent();
    }

    public function test_home_uses_the_real_phone_number(): void
    {
        $html = $this->home();

        $this->assertStringNotContainsString('912345678', $html);
        $this->assertStringContainsString('href="tel:+351928459346" class="cta-phone"', $html);
    }

    public function test_home_heading_levels_do_not_skip(): void
    {
        $html = $this->home();

        // Conteúdo da página (antes do rodapé, que tem a sua própria hierarquia h3 > h4)
        $content = substr($html, 0, strpos($html, '<footer'));
        $this->assertStringNotContainsString('<h4', $content);
        $this->assertStringContainsString('<h3>Processo Transparente</h3>', $html);
        $this->assertSame(1, substr_count($html, '<h1'));
    }

    public function test_mobile_footer_titles_are_h2(): void
    {
        $html = $this->get(route('frontend.privacy'))->assertOk()->getContent();

        $this->assertStringNotContainsString('<h5 class="footer-mobile-title"', $html);
        $this->assertStringContainsString('<h2 class="footer-mobile-title">Contacto</h2>', $html);
    }

    public function test_swiper_css_only_on_pages_with_carousel(): void
    {
        $this->assertStringNotContainsString(self::SWIPER_CSS, $this->home());
        $this->assertStringNotContainsString(self::SWIPER_CSS, $this->get(route('frontend.privacy'))->getContent());
        $this->assertStringNotContainsString(self::SWIPER_CSS, $this->get(route('frontend.terms'))->getContent());

        $vehicle = V3Vehicle::create([
            'reference' => V3Vehicle::generateReference(), 'brand' => 'Porsche', 'model' => 'Taycan',
            'show_online' => true, 'status' => 'em_stock',
        ]);
        $html = $this->get(route('vehicles.details', ['brand' => 'porsche', 'model' => 'taycan', 'id' => $vehicle->reference]))
            ->assertOk()->getContent();
        $this->assertStringContainsString(self::SWIPER_CSS, $html);
    }

    public function test_listing_renders(): void
    {
        $this->get(route('vehicles.list'))->assertOk()->assertDontSee(self::SWIPER_CSS, false);
    }

    public function test_icon_font_uses_font_display_swap_and_files_exist(): void
    {
        $css = file_get_contents(public_path('css/bootstrap-icons.css'));

        $this->assertStringContainsString('font-display: swap', $css);
        $this->assertStringNotContainsString('font-display: block', $css);
        $this->assertFileExists(public_path('fonts/bootstrap-icons.woff2'));
        $this->assertFileExists(public_path('fonts/bootstrap-icons.woff'));
    }

    public function test_thumbnail_service_creates_variant_and_never_touches_original(): void
    {
        Storage::fake('public');
        $file = UploadedFile::fake()->image('p.png', 800, 522);
        $path = $file->storeAs('partners', 'abc.png', 'public');
        $before = Storage::disk('public')->get($path);

        $variant = app(ThumbnailService::class)->generate($path, ThumbnailService::PARTNER_WIDTH);

        $this->assertSame('partners/abc-280w.webp', $variant);
        Storage::disk('public')->assertExists($variant);
        $this->assertSame(280, getimagesizefromstring(Storage::disk('public')->get($variant))[0]);
        $this->assertSame($before, Storage::disk('public')->get($path));

        // Imagens já pequenas ou em falta: sem miniatura, sem erro
        $small = UploadedFile::fake()->image('s.png', 100, 60)->storeAs('partners', 's.png', 'public');
        $this->assertNull(app(ThumbnailService::class)->generate($small, 280));
        $this->assertNull(app(ThumbnailService::class)->generate('partners/nao-existe.png', 280));
    }

    public function test_home_partner_logos_use_thumbnail_when_available_and_lazy_attrs(): void
    {
        Storage::fake('public');
        $with = UploadedFile::fake()->image('a.png', 800, 522)->storeAs('partners', 'with.png', 'public');
        app(ThumbnailService::class)->generate($with, ThumbnailService::PARTNER_WIDTH);
        $without = UploadedFile::fake()->image('b.png', 800, 522)->storeAs('partners', 'without.png', 'public');

        Partner::create(['name' => 'Com', 'image' => $with, 'show_on_site' => true]);
        Partner::create(['name' => 'Sem', 'image' => $without, 'show_on_site' => true]);

        $html = $this->home();

        $this->assertStringContainsString('storage/partners/with-280w.webp', $html);
        $this->assertStringNotContainsString('storage/partners/with.png', $html);
        $this->assertStringContainsString('storage/partners/without.png', $html); // fallback ao original
        $this->assertSame(2, preg_match_all('#<img src="[^"]*partners/[^"]*" alt="[^"]*" width="107" height="70" loading="lazy" decoding="async">#', $html));
    }

    public function test_logo_srcset_only_when_variant_exists(): void
    {
        Storage::fake('public');
        $html = $this->get(route('frontend.privacy'))->assertOk()->getContent();
        $this->assertStringNotContainsString('logo-240w.webp', $html);
        $this->assertStringContainsString('fetchpriority="high"', $html);

        Storage::disk('public')->put('settings/logo-240w.webp', 'x');
        Cache::forget('site_logo_small');

        $html = $this->get(route('frontend.privacy'))->assertOk()->getContent();
        $this->assertStringContainsString('storage/settings/logo-240w.webp', $html);
        $this->assertStringContainsString('240w', $html);
        $this->assertStringContainsString('fetchpriority="high"', $html);
    }
}
