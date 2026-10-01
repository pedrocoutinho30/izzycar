<?php

namespace Tests\Feature;

use App\Permissions\PermissionRegistry;
use App\Permissions\RouteRule;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class PermissionCatalogTest extends TestCase
{
    private function registry(): PermissionRegistry
    {
        return app(PermissionRegistry::class);
    }

    public function test_every_backoffice_route_has_a_rule(): void
    {
        $unmapped = collect(Route::getRoutes()->getRoutes())
            ->filter(fn ($route) => str_starts_with($route->uri(), 'gestao'))
            ->filter(fn ($route) => $this->registry()->ruleForRoute((string) $route->getName(), $route->methods()[0])->type === RouteRule::UNMAPPED)
            ->map(fn ($route) => $route->methods()[0] . ' ' . $route->getName())
            ->values()
            ->all();

        $this->assertSame([], $unmapped, 'Rotas sem regra em config/permissions.php: ' . implode(', ', $unmapped));
    }

    public function test_every_backoffice_route_is_protected_by_the_middleware(): void
    {
        $unprotected = collect(Route::getRoutes()->getRoutes())
            ->filter(fn ($route) => str_starts_with($route->uri(), 'gestao'))
            ->reject(fn ($route) => in_array('authorizeResource', $route->gatherMiddleware(), true))
            ->map(fn ($route) => $route->getName())
            ->values()
            ->all();

        $this->assertSame([], $unprotected);
    }

    public function test_actions_are_inferred_from_route_names(): void
    {
        $rule = fn (string $name, string $method = 'GET') => $this->registry()->ruleForRoute($name, $method)->describe();

        $this->assertSame('leads.view.all', $rule('admin.v2.leads.index'));
        $this->assertSame('leads.create', $rule('admin.v2.leads.store', 'POST'));
        $this->assertSame('leads.update.all', $rule('admin.v2.leads.status', 'POST'));
        $this->assertSame('leads.delete', $rule('admin.v2.leads.destroy', 'DELETE'));
        $this->assertSame('leads.update.all', $rule('admin.v2.leads.assign-owner', 'POST'));
        // Portal do angariador: aceita âmbito próprios.
        $this->assertSame('leads.view', $rule('admin.angariador.leads'));
        $this->assertSame('leads.update', $rule('admin.angariador.leads.status', 'POST'));
        $this->assertSame('proposals.view', $rule('admin.angariador.propostas'));
        // Sub-recursos contam como editar o objeto.
        $this->assertSame('vehicles.update', $rule('admin.v3.vehicles.photos.destroy', 'DELETE'));
        $this->assertSame('form-proposals.update', $rule('admin.v2.form-proposals.opportunities.store', 'POST'));
        // Ferramentas: usar = ver.
        $this->assertSame('reports.view', $rule('admin.v2.reports.generate', 'POST'));
        // Exportações exigem ver todos.
        $this->assertSame('clients.view.all', $rule('admin.v2.export.clients'));
        // O mais específico ganha.
        $this->assertSame('commissions.view.all', $rule('admin.v2.angariadores.comissoes'));
        $this->assertSame('converted-proposals.update', $rule('converted-proposals.updateStatus', 'POST'));
        $this->assertSame(RouteRule::ADMIN_ONLY, $rule('converted-proposals.index'));
        $this->assertSame(RouteRule::ADMIN_ONLY, $rule('admin.v2.angariadores.impersonate', 'POST'));
        $this->assertSame(RouteRule::SHARED, $rule('admin.v2.dashboard'));
        // Ação que o objeto não tem: só admin.
        $this->assertSame(RouteRule::ADMIN_ONLY, $rule('admin.v2.permissions.create'));
    }

    public function test_permission_names_and_profile_grants(): void
    {
        $names = $this->registry()->permissionNames();

        $this->assertContains('leads.view.all', $names);
        $this->assertContains('leads.view.own', $names);
        $this->assertContains('leads.create', $names);
        $this->assertContains('reports.view', $names);
        $this->assertNotContains('reports.create', $names);
        $this->assertContains('form-proposals.create', $names);

        $this->assertEqualsCanonicalizing(
            ['leads.view.own', 'leads.create', 'leads.update.own', 'proposals.view.own', 'proposals.update.own', 'form-proposals.view.own', 'commissions.view.own'],
            $this->registry()->profilePermissions('angariador')
        );
        $importador = $this->registry()->profilePermissions('Importador');
        $this->assertContains('leads.view.all', $importador);
        $this->assertContains('leads.delete', $importador);
        $this->assertNotContains('leads.view.own', $importador);
        $this->assertFalse(collect($importador)->contains(fn ($p) => str_starts_with($p, 'sales.') || str_starts_with($p, 'inspections.') || str_starts_with($p, 'users.')));
    }

    public function test_matrix_round_trip(): void
    {
        $grants = [
            'leads' => ['view' => 'own', 'create' => '1', 'update' => 'all', 'delete' => ''],
            'reports' => ['view' => '1', 'create' => '1'],
            'unknown' => ['view' => '1'],
            'clients' => ['view' => 'bogus'],
        ];
        $names = $this->registry()->grantsToPermissions($grants);

        $this->assertEqualsCanonicalizing(['leads.view.own', 'leads.create', 'leads.update.all', 'reports.view'], $names);
        $this->assertSame(
            ['leads' => ['view' => 'own', 'create' => true, 'update' => 'all'], 'reports' => ['view' => true]],
            $this->registry()->permissionsToGrants($names)
        );
        $this->assertSame(['leads' => ['view' => 'all']], $this->registry()->permissionsToGrants(['leads.view.own', 'leads.view.all']));
    }
}
