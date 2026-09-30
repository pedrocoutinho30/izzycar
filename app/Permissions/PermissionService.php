<?php

namespace App\Permissions;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Routing\Router;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Throwable;

/**
 * Decisões de autorização do backoffice. Um utilizador com vários perfis
 * fica com a soma das permissões e, em cada ação, com o âmbito mais
 * abrangente (todos > próprios). O perfil admin pode tudo.
 */
class PermissionService
{
    private const SCOPE_ORDER = ['all', 'own'];

    /** @var array<string, ?string> */
    private array $scopeCache = [];

    public function __construct(private PermissionRegistry $registry, private Router $router)
    {
    }

    public function isAdmin(User $user): bool
    {
        return $user->hasRole('admin');
    }

    /**
     * Âmbito efetivo: "all", "own", ou null se não tiver a permissão. Ações
     * sem âmbito devolvem "all" quando permitidas.
     */
    public function scope(User $user, string $resource, string $action): ?string
    {
        $key = "{$user->getKey()}:{$resource}:{$action}";
        if (array_key_exists($key, $this->scopeCache)) {
            return $this->scopeCache[$key];
        }

        if (!$this->registry->hasAction($resource, $action)) {
            return $this->scopeCache[$key] = null;
        }

        if ($this->isAdmin($user)) {
            return $this->scopeCache[$key] = 'all';
        }

        $scopes = $this->registry->scopes($resource, $action);
        if ($scopes === []) {
            return $this->scopeCache[$key] = $this->has($user, PermissionRegistry::permissionName($resource, $action)) ? 'all' : null;
        }

        foreach (self::SCOPE_ORDER as $scope) {
            if (in_array($scope, $scopes, true) && $this->has($user, PermissionRegistry::permissionName($resource, $action, $scope))) {
                return $this->scopeCache[$key] = $scope;
            }
        }

        return $this->scopeCache[$key] = null;
    }

    /**
     * Pode fazer a ação? Com $record, e âmbito "próprios", verifica também
     * se o registo é seu. $requiredScope "all" exige o âmbito "todos".
     */
    public function allows(User $user, string $resource, string $action, ?Model $record = null, ?string $requiredScope = null): bool
    {
        $scope = $this->scope($user, $resource, $action);

        if ($scope === null || ($requiredScope === 'all' && $scope !== 'all')) {
            return false;
        }

        return $scope === 'all' || $record === null || $this->owns($user, $resource, $record);
    }

    public function owns(User $user, string $resource, Model $record): bool
    {
        $path = $this->registry->resource($resource)['owner'] ?? null;

        return $path !== null && (int) data_get($record, $path) === (int) $user->getKey();
    }

    /**
     * Restringe uma query aos registos que o utilizador pode ver/editar —
     * usar nas listagens, pesquisas e exportações em vez de filtrar linha a linha.
     */
    public function applyScope(Builder $query, User $user, string $resource, string $action = 'view'): Builder
    {
        $scope = $this->scope($user, $resource, $action);

        if ($scope === 'all') {
            return $query;
        }

        $path = $this->registry->resource($resource)['owner'] ?? null;
        if ($scope === null || $path === null) {
            return $query->whereRaw('1 = 0');
        }

        if (str_contains($path, '.')) {
            $relation = Str::beforeLast($path, '.');
            $column = Str::afterLast($path, '.');

            return $query->whereHas($relation, fn (Builder $q) => $q->where($column, $user->getKey()));
        }

        return $query->where($query->getModel()->qualifyColumn($path), $user->getKey());
    }

    public function allowsRule(User $user, RouteRule $rule): bool
    {
        if ($this->isAdmin($user)) {
            return true;
        }

        return match ($rule->type) {
            RouteRule::SHARED => true,
            RouteRule::RESOURCE => $this->allows($user, $rule->resource, $rule->action, null, $rule->requiredScope),
            default => false,
        };
    }

    public function canAccessRoute(User $user, string $name): bool
    {
        $route = $this->router->getRoutes()->getByName($name);

        return $route !== null && $this->allowsRule($user, $this->registry->ruleForRoute($name, $route->methods()[0]));
    }

    /**
     * Para links/botões montados a partir de um URL (componentes item-card,
     * page-header…). URLs fora do backoffice são sempre permitidos.
     */
    public function canAccessUrl(User $user, string $url, string $method = 'GET'): bool
    {
        try {
            $route = $this->router->getRoutes()->match(Request::create($url, strtoupper($method)));
        } catch (Throwable) {
            return true;
        }

        if (!str_starts_with($route->uri(), 'gestao') || !$route->getName()) {
            return true;
        }

        return $this->allowsRule($user, $this->registry->ruleForRoute($route->getName(), strtoupper($method)));
    }

    /** Verificação sem exceção se a permissão ainda não existir na BD. */
    private function has(User $user, string $permission): bool
    {
        return $user->checkPermissionTo($permission);
    }
}
