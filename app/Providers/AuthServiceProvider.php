<?php

namespace App\Providers;

use App\Models\User;
use App\Permissions\PermissionRegistry;
use App\Permissions\PermissionService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Gate;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array
     */
    protected $policies = [
        // 'App\Model' => 'App\Policies\ModelPolicy',
    ];

    public function register(): void
    {
        $this->app->singleton(PermissionRegistry::class, fn ($app) => new PermissionRegistry($app['config']->get('permissions')));
        $this->app->singleton(PermissionService::class);
    }

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        $this->registerPolicies();

        // O admin pode tudo, incluindo permissões criadas no futuro. As
        // habilidades do catálogo ("leads.view", "leads.update.own", com um
        // registo opcional) resolvem-se pelo PermissionService; o resto segue
        // para o Spatie.
        Gate::before(function (User $user, string $ability, array $arguments) {
            $permissions = app(PermissionService::class);

            if ($permissions->isAdmin($user)) {
                return true;
            }

            $parsed = app(PermissionRegistry::class)->parseAbility($ability);
            if ($parsed === null) {
                return null;
            }

            [$resource, $action, $scope] = $parsed;
            $record = ($arguments[0] ?? null) instanceof Model ? $arguments[0] : null;

            // "leads.view.all" exige o âmbito todos; "leads.view(.own)" aceita
            // qualquer âmbito (e, com registo, verifica se é do utilizador).
            return $permissions->allows($user, $resource, $action, $record, $scope === 'all' ? 'all' : null);
        });

        // @canroute('admin.v2.leads.index') — mostra links/menus só a quem pode abrir a rota.
        Blade::if('canroute', fn (string $name) => auth()->check() && app(PermissionService::class)->canAccessRoute(auth()->user(), $name));
        // @canroutes([...]) — grupos de menu: aparece se alguma das rotas for acessível.
        Blade::if('canroutes', fn (array $names) => auth()->check() && collect($names)->contains(fn ($name) => app(PermissionService::class)->canAccessRoute(auth()->user(), $name)));
        Blade::if('canurl', fn (?string $url, string $method = 'GET') => blank($url) || (auth()->check() && app(PermissionService::class)->canAccessUrl(auth()->user(), $url, $method)));
    }
}
