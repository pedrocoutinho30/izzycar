<?php

namespace Tests\Feature\Mobile;

use App\Providers\RouteServiceProvider;
use App\Support\MobilePwa;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Tests\RefreshesDatabaseWithoutMysqlOnlyMigrations;
use Tests\TestCase;

/**
 * App instalável (PWA) da gestão mobile: manifest, meta tags, regresso depois
 * do login e service worker sem cache de páginas autenticadas.
 */
class MobilePwaTest extends TestCase
{
    use RefreshesDatabaseWithoutMysqlOnlyMigrations;

    protected function setUp(): void
    {
        parent::setUp();
        Cache::forever('frontend_menus', collect());
        Cache::forever('site_logo', '');
    }

    private function manifest(): array
    {
        $json = json_decode(file_get_contents(public_path('mobile-manifest.json')), true);
        $this->assertIsArray($json, 'mobile-manifest.json tem de ser JSON válido.');

        return $json;
    }

    private function login($user, string $password = 'secret')
    {
        return $this->post('/login', ['email' => $user->email, 'password' => $password]);
    }

    // ── Manifest ───────────────────────────────────────────────────────────

    public function test_manifest_mobile_tem_os_campos_da_app(): void
    {
        $m = $this->manifest();

        $this->assertSame('/gestao/mobile', $m['id']);
        $this->assertSame('/gestao/mobile', $m['start_url']);
        $this->assertSame('/', $m['scope']);
        $this->assertSame('standalone', $m['display']);
        $this->assertSame('portrait', $m['orientation']);
        $this->assertSame('IzzyCar Gestão', $m['name']);
        $this->assertSame('IzzyCar', $m['short_name']);
        $this->assertSame('pt-PT', $m['lang']);
        $this->assertSame('#6e0707', $m['theme_color']);
        $this->assertMatchesRegularExpression('/^#[0-9a-f]{6}$/i', $m['background_color']);
        $this->assertNotEmpty($m['description']);
    }

    public function test_icones_do_manifest_existem_com_o_tamanho_declarado_e_ha_maskable(): void
    {
        $icons = $this->manifest()['icons'];
        $purposes = [];

        foreach ($icons as $icon) {
            $path = public_path(ltrim($icon['src'], '/'));
            $this->assertFileExists($path);
            [$w, $h] = getimagesize($path);
            $this->assertSame($icon['sizes'], "{$w}x{$h}", $icon['src']);
            $this->assertSame('image/png', $icon['type']);
            foreach (explode(' ', $icon['purpose'] ?? 'any') as $p) {
                $purposes[$p][] = $icon['sizes'];
            }
        }

        foreach (['192x192', '512x512'] as $size) {
            $this->assertContains($size, $purposes['any'] ?? []);
            $this->assertContains($size, $purposes['maskable'] ?? []);
        }

        // O iPhone usa o apple-touch-icon de 180px: tem de ser opaco.
        $this->assertSame([180, 180], array_slice(getimagesize(public_path('img/mobile/icon-180.png')), 0, 2));
    }

    public function test_atalhos_do_manifest_apontam_para_rotas_mobile_existentes(): void
    {
        foreach ($this->manifest()['shortcuts'] ?? [] as $shortcut) {
            $this->assertTrue(MobilePwa::isMobilePath($shortcut['url']), $shortcut['url']);
            $route = app('router')->getRoutes()->match(Request::create($shortcut['url']));
            $this->assertStringStartsWith('admin.mobile.', $route->getName());
            foreach ($shortcut['icons'] ?? [] as $icon) {
                $this->assertFileExists(public_path(ltrim($icon['src'], '/')));
            }
        }
    }

    // ── Meta tags ──────────────────────────────────────────────────────────

    public function test_paginas_mobile_tem_um_so_manifest_e_as_meta_tags_da_app(): void
    {
        $html = $this->actingAs($this->mobileUser())->get('/gestao/mobile')->assertOk()->getContent();

        $this->assertSame(1, substr_count($html, 'rel="manifest"'));
        $this->assertStringContainsString('rel="manifest" href="' . asset('mobile-manifest.json') . '"', $html);
        $this->assertStringContainsString('apple-mobile-web-app-capable" content="yes"', $html);
        $this->assertStringContainsString('apple-mobile-web-app-title" content="IzzyCar Gestão"', $html);
        $this->assertStringContainsString('apple-mobile-web-app-status-bar-style"', $html);
        $this->assertStringContainsString('rel="apple-touch-icon" href="' . asset('img/mobile/icon-180.png') . '"', $html);
        $this->assertStringContainsString('name="theme-color" content="#6e0707"', $html);
        $this->assertStringContainsString('viewport-fit=cover', $html);
        // A app mobile não regista service worker próprio.
        $this->assertStringNotContainsString('serviceWorker', $html);
    }

    public function test_login_vindo_da_area_mobile_leva_o_manifest_mobile(): void
    {
        $this->get('/gestao/mobile/leads')->assertRedirect('/login');

        $html = $this->get('/login')->assertOk()->getContent();
        $this->assertSame(1, substr_count($html, 'rel="manifest"'));
        $this->assertStringContainsString('rel="manifest" href="' . asset('mobile-manifest.json') . '"', $html);
        $this->assertStringContainsString('apple-mobile-web-app-title" content="IzzyCar Gestão"', $html);
    }

    public function test_login_desktop_fica_igual_sem_manifest_mobile(): void
    {
        // Sem destino pendente.
        $this->get('/login')->assertOk()
            ->assertDontSee('mobile-manifest.json', false)
            ->assertDontSee('rel="manifest"', false);

        // Destino desktop.
        $this->get('/gestao/v2/dashboard')->assertRedirect('/login');
        $this->get('/login')->assertOk()->assertDontSee('mobile-manifest.json', false);
    }

    public function test_login_com_destino_mobile_de_outra_origem_nao_leva_o_manifest_mobile(): void
    {
        $this->withSession(['url.intended' => 'https://evil.example/gestao/mobile'])
            ->get('/login')->assertOk()->assertDontSee('mobile-manifest.json', false);
    }

    // ── Fluxo de autenticação ──────────────────────────────────────────────

    public function test_visitante_vai_para_o_login_e_fica_com_o_destino_guardado(): void
    {
        $this->get('/gestao/mobile')->assertRedirect('/login');
        $this->assertSame(url('/gestao/mobile'), session('url.intended'));

        $this->get('/gestao/mobile/tarefas')->assertRedirect('/login');
        $this->assertSame(url('/gestao/mobile/tarefas'), session('url.intended'));
    }

    public function test_login_com_access_mobile_regressa_ao_mobile(): void
    {
        $user = $this->mobileUser();

        $this->get('/gestao/mobile')->assertRedirect('/login');
        $this->login($user)->assertRedirect(url('/gestao/mobile'));
        $this->assertAuthenticatedAs($user);

        $this->get('/gestao/mobile')->assertOk();
    }

    public function test_login_regressa_a_subpagina_mobile_pretendida(): void
    {
        $user = $this->mobileUser();

        $this->get('/gestao/mobile/leads')->assertRedirect('/login');
        $this->login($user)->assertRedirect(url('/gestao/mobile/leads'));
        $this->get('/gestao/mobile/leads')->assertOk();
    }

    public function test_login_sem_access_mobile_acaba_no_desktop_com_aviso_e_sem_ciclo(): void
    {
        $user = $this->mobileUser(false);

        $this->get('/gestao/mobile')->assertRedirect('/login');
        $response = $this->login($user)->assertRedirect(url('/gestao/mobile'));

        // Segue os redirects à mão: tem de parar em no máximo 3 saltos.
        $visited = [];
        $hops = 0;
        $flash = null;
        while ($response->isRedirect() && $hops < 3) {
            $location = $response->headers->get('Location');
            $visited[] = $location;
            $response = $this->get($location);
            $flash ??= session('error');
            $hops++;
        }

        $response->assertOk();
        $this->assertLessThanOrEqual(2, $hops, 'Cadeia de redirects: ' . implode(' → ', $visited));
        $this->assertSame(route('admin.v2.dashboard'), end($visited));
        $this->assertSame(count($visited), count(array_unique($visited)), 'Ciclo de redirects.');
        // O aviso é posto na sessão pelo EnsureMobileAccess (o dashboard desktop, hoje, não o mostra).
        $this->assertSame('Não tem acesso à versão mobile.', $flash);
    }

    public function test_angariador_com_access_mobile_continua_barrado(): void
    {
        $angariador = $this->backofficeUser('angariador');
        $angariador->forceFill(['access_mobile' => true])->save();

        $this->actingAs($angariador)->get('/gestao/mobile')->assertForbidden();
    }

    public function test_destino_de_outra_origem_e_ignorado_no_login(): void
    {
        foreach (['https://evil.example/gestao/mobile', '//evil.example/gestao/mobile', 'javascript:alert(1)'] as $evil) {
            $user = $this->mobileUser();

            $this->withSession(['url.intended' => $evil]);
            $this->login($user)->assertRedirect(RouteServiceProvider::HOME);

            auth()->logout();
            $this->flushSession();
        }
    }

    public function test_login_desktop_sem_destino_vai_para_home(): void
    {
        $this->login($this->mobileUser())->assertRedirect(RouteServiceProvider::HOME);
        $this->post('/logout');

        $this->login($this->mobileUser(false))->assertRedirect(RouteServiceProvider::HOME);
    }

    public function test_login_desktop_com_destino_desktop_regressa_ao_destino(): void
    {
        $this->get('/gestao/v2/leads')->assertRedirect('/login');
        $this->login($this->mobileUser())->assertRedirect(url('/gestao/v2/leads'));
    }

    public function test_com_sessao_abrir_login_com_destino_mobile_volta_ao_mobile(): void
    {
        $user = $this->mobileUser();

        $this->actingAs($user)
            ->withSession(['url.intended' => url('/gestao/mobile/leads')])
            ->get('/login')
            ->assertRedirect(url('/gestao/mobile/leads'));
        $this->assertNull(session('url.intended'));
    }

    public function test_com_sessao_abrir_login_fica_como_dantes_nos_outros_casos(): void
    {
        // Sem destino pendente.
        $this->actingAs($this->mobileUser())->get('/login')->assertRedirect(RouteServiceProvider::HOME);

        // Destino mobile mas sem access_mobile.
        $this->actingAs($this->mobileUser(false))
            ->withSession(['url.intended' => url('/gestao/mobile')])
            ->get('/login')->assertRedirect(RouteServiceProvider::HOME);

        // Destino desktop.
        $this->actingAs($this->mobileUser())
            ->withSession(['url.intended' => url('/gestao/v2/leads')])
            ->get('/login')->assertRedirect(RouteServiceProvider::HOME);

        // Destino de outra origem.
        $this->actingAs($this->mobileUser())
            ->withSession(['url.intended' => 'https://evil.example/gestao/mobile'])
            ->get('/login')->assertRedirect(RouteServiceProvider::HOME);
    }

    public function test_regras_de_origem_e_de_caminho_mobile(): void
    {
        $request = Request::create(url('/login'));

        foreach ([url('/gestao/mobile'), '/gestao/mobile/leads', url('/gestao/v2/dashboard')] as $ok) {
            $this->assertTrue(MobilePwa::isSameOrigin($ok, $request), $ok);
        }
        foreach (['https://evil.example/gestao/mobile', '//evil.example/x', '/\\evil.example', 'javascript:alert(1)', 'ftp://localhost/x', ''] as $bad) {
            $this->assertFalse(MobilePwa::isSameOrigin($bad, $request), $bad);
        }

        $this->assertTrue(MobilePwa::isMobilePath('/gestao/mobile'));
        $this->assertTrue(MobilePwa::isMobilePath('/gestao/mobile/leads/3'));
        $this->assertFalse(MobilePwa::isMobilePath('/gestao/mobilex'));
        $this->assertFalse(MobilePwa::isMobilePath('/gestao/v2/dashboard'));
    }

    // ── Cache ──────────────────────────────────────────────────────────────

    public function test_paginas_mobile_nao_podem_ir_para_caches_partilhadas(): void
    {
        $user = $this->mobileUser();

        foreach (['/gestao/mobile', '/gestao/mobile/leads', '/gestao/mobile/tarefas'] as $path) {
            $cacheControl = (string) $this->actingAs($user)->get($path)->assertOk()->headers->get('Cache-Control');
            $this->assertStringContainsString('private', $cacheControl, $path);
            $this->assertStringNotContainsString('public', $cacheControl, $path);
        }
    }

    public function test_service_worker_nao_guarda_paginas_autenticadas(): void
    {
        $sw = file_get_contents(public_path('sw.js'));

        // Versão nova: o activate apaga as caches antigas (que podiam ter /gestao).
        $this->assertMatchesRegularExpression("/const CACHE_VERSION = 'izzycar-v(\\d+)'/", $sw);
        preg_match("/const CACHE_VERSION = 'izzycar-v(\\d+)'/", $sw, $m);
        $this->assertGreaterThanOrEqual(2, (int) $m[1]);
        $this->assertStringContainsString('.filter((key) => key !== CACHE_VERSION)', $sw);

        // Áreas privadas excluídas.
        foreach (["'/gestao'", "'/login'", "'/logout'", "'/password'", "'/two-factor'"] as $path) {
            $this->assertStringContainsString($path, $sw);
        }

        // Só GET; respostas no-store/private não vão para a cache.
        $this->assertStringContainsString("if (request.method !== 'GET') return;", $sw);
        $this->assertStringContainsString('no-store|private', $sw);

        // Deixou de haver o "resto: network-first com cache" e só há um cache.put
        // (assets estáticos) — nenhum no ramo das navegações.
        $this->assertStringNotContainsString('.catch(() => caches.match(request))', $sw);
        $this->assertSame(1, substr_count($sw, 'cache.put('));
    }
}
