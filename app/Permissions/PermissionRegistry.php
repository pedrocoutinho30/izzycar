<?php

namespace App\Permissions;

use Illuminate\Support\Collection;
use Illuminate\Support\Str;

/**
 * Leitura do catálogo config/permissions.php: objetos, ações, âmbitos,
 * nomes das permissões e a regra aplicável a cada rota do backoffice.
 */
class PermissionRegistry
{
    private const DEFAULT_ACTIONS = ['view' => [], 'create' => [], 'update' => [], 'delete' => []];

    private const VIEW_SUFFIXES = ['index', 'show', 'list', 'json', 'kanban', 'results', 'download', 'report', 'preview', 'map', 'map-data'];

    /** @var array<string, array>|null */
    private ?array $resources = null;

    /** @var array<string, RouteRule> */
    private array $routeRules = [];

    public function __construct(private array $config)
    {
    }

    /**
     * Objetos indexados pela chave, com "module" e "module_label".
     *
     * @return array<string, array{label: string, module: string, module_label: string, actions: array<string, list<string>>, owner: ?string, routes: list<string>, own_routes: list<string>, map_all_to: ?string}>
     */
    public function resources(): array
    {
        if ($this->resources !== null) {
            return $this->resources;
        }

        $this->resources = [];
        foreach ($this->config['modules'] as $moduleKey => $module) {
            foreach ($module['resources'] as $key => $resource) {
                $this->resources[$key] = [
                    'label' => $resource['label'],
                    'module' => $moduleKey,
                    'module_label' => $module['label'],
                    'actions' => $resource['actions'] ?? self::DEFAULT_ACTIONS,
                    'owner' => $resource['owner'] ?? null,
                    'routes' => $resource['routes'] ?? [],
                    'own_routes' => $resource['own_routes'] ?? [],
                    'map_all_to' => $resource['map_all_to'] ?? null,
                ];
            }
        }

        return $this->resources;
    }

    /** @return Collection<string, Collection<string, array>> objetos agrupados por módulo */
    public function modules(): Collection
    {
        return collect($this->resources())->groupBy('module', preserveKeys: true);
    }

    public function moduleLabel(string $module): string
    {
        return $this->config['modules'][$module]['label'] ?? $module;
    }

    public function resource(string $key): ?array
    {
        return $this->resources()[$key] ?? null;
    }

    /** @return list<string> âmbitos da ação ([] = sem âmbito) */
    public function scopes(string $resource, string $action): array
    {
        return $this->resource($resource)['actions'][$action] ?? [];
    }

    public function hasAction(string $resource, string $action): bool
    {
        return array_key_exists($action, $this->resource($resource)['actions'] ?? []);
    }

    public function actionLabel(string $action): string
    {
        return $this->config['actions'][$action] ?? $action;
    }

    public function scopeLabel(string $scope): string
    {
        return $this->config['scopes'][$scope] ?? $scope;
    }

    public static function permissionName(string $resource, string $action, ?string $scope = null): string
    {
        return $scope ? "{$resource}.{$action}.{$scope}" : "{$resource}.{$action}";
    }

    /** @return list<string> todas as permissões do catálogo */
    public function permissionNames(): array
    {
        $names = [];
        foreach ($this->resources() as $key => $resource) {
            foreach ($resource['actions'] as $action => $scopes) {
                if ($scopes === []) {
                    $names[] = self::permissionName($key, $action);
                } else {
                    foreach ($scopes as $scope) {
                        $names[] = self::permissionName($key, $action, $scope);
                    }
                }
            }
        }

        return $names;
    }

    public function isCatalogPermission(string $name): bool
    {
        return in_array($name, $this->permissionNames(), true);
    }

    /**
     * "leads.view" ou "leads.view.own" → [resource, action, scope|null], ou
     * null se não for uma habilidade do catálogo.
     *
     * @return array{0: string, 1: string, 2: ?string}|null
     */
    public function parseAbility(string $ability): ?array
    {
        $parts = explode('.', $ability);
        if (count($parts) < 2 || count($parts) > 3 || !$this->hasAction($parts[0], $parts[1])) {
            return null;
        }

        $scope = $parts[2] ?? null;
        if ($scope !== null && !in_array($scope, $this->scopes($parts[0], $parts[1]), true)) {
            return null;
        }

        return [$parts[0], $parts[1], $scope];
    }

    /**
     * Permissões de um perfil definido em config("permissions.profiles").
     *
     * @return list<string>
     */
    public function profilePermissions(string $profile): array
    {
        $names = [];
        foreach ($this->config['profiles'][$profile] ?? [] as $resource => $grant) {
            $actions = $this->resource($resource)['actions'] ?? [];
            $grant = $grant === '*' ? array_map(fn ($scopes) => $scopes === [] ? true : $scopes[0], $actions) : $grant;

            foreach ($grant as $action => $value) {
                if ($value === false || !array_key_exists($action, $actions)) {
                    continue;
                }
                $names[] = self::permissionName($resource, $action, $actions[$action] === [] ? null : $value);
            }
        }

        return $names;
    }

    /**
     * Matriz do formulário de perfil → permissões. Valores por ação: "all" /
     * "own" (ações com âmbito) ou "1" (ações simples); vazio = sem acesso.
     * Valores fora do catálogo são ignorados.
     *
     * @param  array<string, array<string, string|null>>  $grants
     * @return list<string>
     */
    public function grantsToPermissions(array $grants): array
    {
        $names = [];
        foreach ($grants as $resource => $actions) {
            foreach ((array) $actions as $action => $value) {
                if (blank($value) || !$this->hasAction($resource, $action)) {
                    continue;
                }
                $scopes = $this->scopes($resource, $action);
                if ($scopes === []) {
                    $names[] = self::permissionName($resource, $action);
                } elseif (in_array($value, $scopes, true)) {
                    $names[] = self::permissionName($resource, $action, $value);
                }
            }
        }

        return array_values(array_unique($names));
    }

    /**
     * Permissões → matriz ([objeto][ação] = "all" | "own" | true), com o
     * âmbito mais abrangente quando há vários.
     *
     * @param  iterable<string>  $names
     * @return array<string, array<string, string|bool>>
     */
    public function permissionsToGrants(iterable $names): array
    {
        $grants = [];
        foreach ($names as $name) {
            if (!$parsed = $this->parseAbility($name)) {
                continue;
            }
            [$resource, $action, $scope] = $parsed;
            if ($scope === null) {
                if ($this->scopes($resource, $action) === []) {
                    $grants[$resource][$action] = true;
                }
            } elseif (($grants[$resource][$action] ?? null) !== 'all') {
                $grants[$resource][$action] = $scope;
            }
        }

        return $grants;
    }

    /** @return list<string> */
    public function profileNames(): array
    {
        return array_keys($this->config['profiles'] ?? []);
    }

    /**
     * Regra de acesso de uma rota. A correspondência mais específica ganha
     * (nome exato > padrão com prefixo mais longo).
     */
    public function ruleForRoute(string $name, string $method = 'GET'): RouteRule
    {
        $cacheKey = $method . ' ' . $name;
        if (isset($this->routeRules[$cacheKey])) {
            return $this->routeRules[$cacheKey];
        }

        $best = null;
        $bestWeight = -1;
        $consider = function (string $pattern, callable $make) use ($name, &$best, &$bestWeight) {
            if (!Str::is($pattern, $name)) {
                return;
            }
            $weight = str_contains($pattern, '*') ? strlen(rtrim($pattern, '*')) : PHP_INT_MAX;
            if ($weight > $bestWeight) {
                $bestWeight = $weight;
                $best = $make($pattern);
            }
        };

        foreach ($this->resources() as $key => $resource) {
            foreach ($resource['routes'] as $pattern) {
                $consider($pattern, fn ($p) => [$key, $p, false]);
            }
            foreach ($resource['own_routes'] as $pattern) {
                $consider($pattern, fn ($p) => [$key, $p, true]);
            }
        }
        foreach ($this->config['shared'] as $pattern) {
            $consider($pattern, fn () => 'shared');
        }
        foreach ($this->config['admin_only'] as $pattern) {
            $consider($pattern, fn () => 'admin');
        }

        return $this->routeRules[$cacheKey] = match (true) {
            $best === null => RouteRule::unmapped(),
            $best === 'shared' => RouteRule::shared(),
            $best === 'admin' => RouteRule::adminOnly(),
            default => $this->resourceRule($name, $method, ...$best),
        };
    }

    private function resourceRule(string $name, string $method, string $resource, string $pattern, bool $ownRoute): RouteRule
    {
        [$action, $requiredScope] = $this->actionForRoute($name, $method, $pattern, $resource);

        if (!$this->hasAction($resource, $action)) {
            return RouteRule::adminOnly();
        }

        if ($requiredScope === null && $this->scopes($resource, $action) !== [] && !$ownRoute) {
            // Rotas que não filtram pelo dono só servem a quem vê/edita todos.
            $requiredScope = 'all';
        }

        return RouteRule::resource($resource, $action, $requiredScope);
    }

    /** @return array{0: string, 1: ?string} */
    private function actionForRoute(string $name, string $method, string $pattern, string $resource): array
    {
        foreach ($this->config['route_actions'] as $routePattern => $override) {
            if (Str::is($routePattern, $name)) {
                $parts = explode('.', $override);

                return [$parts[0], $parts[1] ?? null];
            }
        }

        if ($mapped = $this->resource($resource)['map_all_to']) {
            return [$mapped, null];
        }

        $isGet = in_array(strtoupper($method), ['GET', 'HEAD'], true);
        $remainder = str_contains($pattern, '*')
            ? substr($name, strlen(rtrim($pattern, '*')))
            : Str::afterLast($name, '.');
        $segments = explode('.', $remainder);

        // Sub-recurso (ex. vehicles.photos.cover): editar o objeto principal.
        if (count($segments) > 1) {
            return [$isGet ? 'view' : 'update', null];
        }

        return [match (true) {
            in_array($remainder, ['create', 'store'], true) => 'create',
            in_array($remainder, ['destroy', 'delete'], true) => 'delete',
            $remainder === 'edit', $remainder === 'update' => 'update',
            $isGet => 'view',
            default => 'update',
        }, null];
    }
}
