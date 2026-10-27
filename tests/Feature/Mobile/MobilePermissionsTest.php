<?php

namespace Tests\Feature\Mobile;

use App\Models\FormProposal;
use App\Models\User;
use Illuminate\Routing\Route;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Route as RouteFacade;
use Tests\RefreshesDatabaseWithoutMysqlOnlyMigrations;
use Tests\TestCase;

/**
 * Percorre TODAS as rotas admin.mobile.* (incluindo as que vierem a ser
 * criadas): nenhuma pode ficar sem auth + mobile.access, nem abrir a um
 * visitante, a quem não tem access_mobile ou a um angariador puro.
 */
class MobilePermissionsTest extends TestCase
{
    use RefreshesDatabaseWithoutMysqlOnlyMigrations;

    private ?array $ids = null;

    protected function setUp(): void
    {
        parent::setUp();
        Cache::forever('frontend_menus', collect());
        Cache::forever('site_logo', '');
    }

    /** @return array<string, Route> */
    private function mobileRoutes(): array
    {
        $routes = collect(RouteFacade::getRoutes()->getRoutes())
            ->filter(fn (Route $route) => str_starts_with((string) $route->getName(), 'admin.mobile.'))
            ->keyBy(fn (Route $route) => $route->getName())
            ->all();

        $this->assertGreaterThan(30, count($routes), 'Esperava encontrar as rotas mobile.');

        return $routes;
    }

    /**
     * URL com ids reais para os parâmetros: algumas rotas usam model binding
     * (com scopeBindings), que pode correr antes dos middlewares do grupo.
     */
    private function urlFor(Route $route): string
    {
        $ids = $this->ids ??= $this->bindingIds();

        $parameters = [];
        foreach ($route->parameterNames() as $name) {
            $parameters[$name] = $ids[$name] ?? 999999;
        }

        return route($route->getName(), $parameters);
    }

    private function bindingIds(): array
    {
        $formProposal = FormProposal::create(['name' => 'Ana', 'email' => 'ana@example.com', 'phone' => '912345678', 'brand' => 'Tesla', 'model' => 'Model 3']);
        $opportunity = $formProposal->opportunities()->create(['brand' => 'Tesla', 'model' => 'Model 3', 'year' => 2022, 'price' => 23900, 'fuel' => 'gasolina']);
        $contact = $opportunity->contacts()->create(['contacted_at' => now(), 'method' => 'email', 'type' => 'nota']);

        return ['id' => 999999, 'formProposal' => $formProposal->id, 'opportunity' => $opportunity->id, 'contact' => $contact->id];
    }

    public function test_todas_as_rotas_mobile_tem_auth_e_mobile_access(): void
    {
        foreach ($this->mobileRoutes() as $name => $route) {
            $middleware = $route->gatherMiddleware();
            $this->assertContains('auth', $middleware, "{$name} sem auth");
            $this->assertContains('mobile.access', $middleware, "{$name} sem mobile.access");
            $this->assertContains('restrictAngariador', $middleware, "{$name} sem restrictAngariador");
        }
    }

    public function test_visitante_vai_para_o_login_em_todas(): void
    {
        foreach ($this->mobileRoutes() as $name => $route) {
            $this->callRoute($route)->assertRedirect(route('login'));
        }
    }

    public function test_sem_access_mobile_volta_ao_desktop_em_todas_sem_ciclo(): void
    {
        $user = $this->mobileUser(false);

        foreach ($this->mobileRoutes() as $name => $route) {
            $response = $this->callRoute($route, $user);
            $this->assertTrue($response->isRedirect(route('admin.v2.dashboard')), "{$name}: esperava redirect para o dashboard desktop, veio {$response->getStatusCode()}");
            $response->assertSessionHas('error', 'Não tem acesso à versão mobile.');
        }

        // O destino está fora do grupo mobile: abre sem novo redirect.
        $this->actingAs($user)->get(route('admin.v2.dashboard'))->assertOk();
    }

    public function test_angariador_puro_com_access_mobile_fica_barrado_em_todas(): void
    {
        $angariador = $this->backofficeUser('angariador');
        $angariador->forceFill(['access_mobile' => true])->save();

        foreach ($this->mobileRoutes() as $name => $route) {
            $status = $this->callRoute($route, $angariador)->getStatusCode();
            $this->assertSame(403, $status, "{$name}: angariador puro devia receber 403, veio {$status}");
        }
    }

    private function callRoute(Route $route, ?User $user = null)
    {
        $method = collect($route->methods())->reject(fn ($m) => $m === 'HEAD')->first();

        if ($user) {
            $this->actingAs($user);
        }

        return $this->call($method, $this->urlFor($route));
    }
}
