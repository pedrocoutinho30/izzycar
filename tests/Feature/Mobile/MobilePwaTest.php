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

        // O iPhone usa o apple-touch-icon de 180px (o tamanho é verificado aqui;
        // a opacidade no teste seguinte).
        $this->assertSame([180, 180], array_slice(getimagesize(public_path('img/mobile/icon-180.png')), 0, 2));
    }

    public function test_apple_touch_icon_e_opaco(): void
    {
        if (!function_exists('imagecreatefrompng')) {
            $this->markTestSkipped('Extensão GD não disponível.');
        }

        // O iPhone pinta a preto os píxeis transparentes do apple-touch-icon: nenhum
        // píxel pode ter alpha (no GD, 0 = opaco, 127 = transparente).
        $img = imagecreatefrompng(public_path('img/mobile/icon-180.png'));
        $maxAlpha = 0;
        for ($x = 0; $x < imagesx($img); $x++) {
            for ($y = 0; $y < imagesy($img); $y++) {
                $maxAlpha = max($maxAlpha, (imagecolorat($img, $x, $y) >> 24) & 0x7F);
            }
        }
        $this->assertSame(0, $maxAlpha, 'icon-180.png tem píxeis transparentes.');
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

    public function test_login_sem_access_mobile_vai_direto_ao_dashboard_desktop(): void
    {
        $user = $this->mobileUser(false);

        $this->get('/gestao/mobile')->assertRedirect('/login');
        // O destino mobile é descartado: sem o salto /gestao/mobile → dashboard.
        $this->login($user)->assertRedirect(RouteServiceProvider::HOME);
        $this->assertSame(url(RouteServiceProvider::HOME), route('admin.v2.dashboard'));
        $this->assertNull(session('url.intended'));
        $this->assertAuthenticatedAs($user);

        $this->get(route('admin.v2.dashboard'))->assertOk();
    }

    public function test_angariador_com_destino_mobile_entra_no_dashboard_do_angariador(): void
    {
        $angariador = $this->backofficeUser('angariador');
        $angariador->forceFill(['access_mobile' => true])->save();
        $this->assertTrue($angariador->fresh()->isAngariadorOnly());

        $this->get('/gestao/mobile/leads')->assertRedirect('/login');
        $response = $this->login($angariador)->assertRedirect(route('admin.angariador.dashboard'));
        $this->assertNull(session('url.intended'));

        $this->get($response->headers->get('Location'))->assertOk();
    }

    public function test_destino_desktop_continua_a_valer_para_quem_nao_tem_access_mobile(): void
    {
        $this->get('/gestao/v2/leads')->assertRedirect('/login');
        $this->login($this->mobileUser(false))->assertRedirect(url('/gestao/v2/leads'));
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

        $host = parse_url(url('/'), PHP_URL_HOST);
        $scheme = parse_url(url('/'), PHP_URL_SCHEME);
        foreach ([
            "{$scheme}://{$host}@evil.com/gestao/mobile",   // userinfo: o host real é evil.com
            "{$scheme}://evil.com@{$host}/gestao/mobile",   // userinfo com o nosso host: recusado na mesma
            "{$scheme}://user:pass@{$host}/gestao/mobile",
            "{$scheme}://{$host}:8443/gestao/mobile",       // outra porta = outra origem
            'https:/evil.com/gestao/mobile',                // o browser lê https://evil.com
            'https:evil.com/gestao/mobile',
            "{$scheme}://{$host}.evil.com/gestao/mobile",
            "\t/gestao/mobile",
        ] as $bad) {
            $this->assertFalse(MobilePwa::isSameOrigin($bad, $request), $bad);
        }

        // Esquema/host em maiúsculas continua a ser a mesma origem.
        $this->assertTrue(MobilePwa::isSameOrigin(strtoupper("{$scheme}://{$host}") . '/gestao/mobile', $request));

        $this->assertTrue(MobilePwa::isMobilePath('/gestao/mobile'));
        $this->assertTrue(MobilePwa::isMobilePath('/gestao/mobile/leads/3'));
        $this->assertFalse(MobilePwa::isMobilePath('/gestao/mobilex'));
        $this->assertFalse(MobilePwa::isMobilePath('/gestao/mobile.evil'));
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

    public function test_service_worker_versao_nova_apaga_caches_antigas_e_instala_sem_falhar(): void
    {
        $sw = file_get_contents(public_path('sw.js'));

        // v3: o activate apaga tudo o que não seja a versão atual (v1 tinha JSON de
        // /gestao, v2 ficheiros de /storage), antes e depois do clients.claim().
        $this->assertStringContainsString("const CACHE_VERSION = 'izzycar-v3';", $sw);
        $this->assertStringContainsString('.filter((key) => key !== CACHE_VERSION)', $sw);
        $this->assertMatchesRegularExpression('/purgeOldCaches\(\)\s*\.then\(\(\) => self\.clients\.claim\(\)\)\s*\.then\(purgeOldCaches\)/', $sw);

        // Install tolerante: cada asset à parte, nunca addAll (um 404 no offline.html
        // deixava o worker antigo a controlar as páginas).
        $this->assertStringNotContainsString('addAll(', $sw);
        $this->assertStringContainsString('Promise.allSettled(', $sw);
        $this->assertStringContainsString('self.skipWaiting()', $sw);

        // Push e cliques nas notificações continuam lá.
        $this->assertStringContainsString("addEventListener('push'", $sw);
        $this->assertStringContainsString("addEventListener('notificationclick'", $sw);
    }

    /**
     * Corre as funções de decisão do sw.js em node (o ficheiro exporta-as quando
     * há `module`; no browser esse bloco não corre).
     */
    public function test_service_worker_so_guarda_assets_estaticos_do_site(): void
    {
        $node = (new \Symfony\Component\Process\ExecutableFinder())->find('node');
        if ($node === null) {
            $this->markTestSkipped('node não está instalado.');
        }

        $privados = [
            '/gestao/mobile', '/gestao/mobile/leads', '/gestao/v2/leads', '/gestao/v2/leads/3.json',
            '/login', '/logout', '/password/reset', '/definir-password/abc',
            '/storage/photos/1/x.jpg', '/storage/photos/12_Joao/carta.pdf', '/storage/inspections/1.jpg',
            '/storage/import-opportunities/9/foto.png', '/uploads/photos/1/x.jpg',
            '/index.php/gestao/x', '/gest%61o/x', '/GESTAO/x', '//gestao/x', '/gestao%2Fmobile',
            '/index.php/css/app.css', '/css/%2e%2e/storage/photos/1/x.jpg', '/img/..%2Fstorage/x.jpg',
            '/css/%E0%A4%A', '/', '/viaturas', '/api/x', '/laravel-filemanager/x.js', '/x.css', '/sw.js',
        ];
        $estaticos = ['/css/app.css', '/img/logo-simples.png', '/fonts/x.woff2', '/js/x.js', '/images/default-car.jpg', '/vendor/jquery/jquery.min.js'];

        $script = <<<'JS'
const sw = require(process.argv[1]);
const cases = JSON.parse(process.argv[2]);
const origin = 'https://izzycar.test';
const req = (path, extra = {}) => ({ method: 'GET', mode: 'no-cors', url: origin + path, ...extra });
const resp = (extra = {}, headers = {}) => ({ status: 200, type: 'basic', redirected: false, headers: new Headers(headers), ...extra });
const out = { paths: {}, privados: {}, outros: {} };
for (const p of cases.paths) out.paths[p] = sw.isCacheableRequest(req(p), origin);
for (const p of cases.privadosCheck) out.privados[p] = sw.isPrivatePath(p);
Object.assign(out.outros, {
  version: sw.CACHE_VERSION,
  post: sw.isCacheableRequest(req('/css/app.css', { method: 'POST' }), origin),
  head: sw.isCacheableRequest(req('/css/app.css', { method: 'HEAD' }), origin),
  navigate: sw.isCacheableRequest(req('/css/app.css', { mode: 'navigate' }), origin),
  crossOrigin: sw.isCacheableRequest({ method: 'GET', url: 'https://evil.com/css/app.css' }, origin),
  ok: sw.isCacheableResponse(resp({}, { 'Cache-Control': 'public, max-age=31536000, immutable' })),
  semHeaders: sw.isCacheableResponse(resp()),
  private: sw.isCacheableResponse(resp({}, { 'Cache-Control': 'no-cache, private' })),
  noStore: sw.isCacheableResponse(resp({}, { 'Cache-Control': 'no-store' })),
  setCookie: sw.isCacheableResponse(resp({}, { 'Set-Cookie': 'laravel_session=x' })),
  redirected: sw.isCacheableResponse(resp({ redirected: true })),
  status404: sw.isCacheableResponse(resp({ status: 404 })),
  status206: sw.isCacheableResponse(resp({ status: 206 })),
  opaque: sw.isCacheableResponse(resp({ type: 'opaque', status: 0 })),
  cors: sw.isCacheableResponse(resp({ type: 'cors' })),
  vazio: sw.isCacheableResponse(null),
});
process.stdout.write(JSON.stringify(out));
JS;

        $process = new \Symfony\Component\Process\Process([
            $node, '-e', $script, public_path('sw.js'),
            json_encode(['paths' => array_merge($privados, $estaticos), 'privadosCheck' => ['/index.php/gestao/x', '/gest%61o/x', '/GESTAO/x', '/Login', '/storage/x', '/gestao%', '/css/app.css']]),
        ]);
        $process->mustRun();
        $out = json_decode($process->getOutput(), true);
        $this->assertIsArray($out, $process->getOutput() . $process->getErrorOutput());

        foreach ($privados as $path) {
            $this->assertFalse($out['paths'][$path], "Não pode ir para a cache: {$path}");
        }
        foreach ($estaticos as $path) {
            $this->assertTrue($out['paths'][$path], "Asset estático devia poder ir para a cache: {$path}");
        }

        foreach (['/index.php/gestao/x', '/gest%61o/x', '/GESTAO/x', '/Login', '/storage/x', '/gestao%'] as $path) {
            $this->assertTrue($out['privados'][$path], "Devia contar como privado: {$path}");
        }
        $this->assertFalse($out['privados']['/css/app.css']);

        $this->assertSame('izzycar-v3', $out['outros']['version']);
        foreach (['post', 'head', 'navigate', 'crossOrigin'] as $caso) {
            $this->assertFalse($out['outros'][$caso], "Pedido não guardável: {$caso}");
        }
        $this->assertTrue($out['outros']['ok']);
        $this->assertTrue($out['outros']['semHeaders']);
        foreach (['private', 'noStore', 'setCookie', 'redirected', 'status404', 'status206', 'opaque', 'cors', 'vazio'] as $caso) {
            $this->assertFalse($out['outros'][$caso], "Resposta não guardável: {$caso}");
        }
    }
}
