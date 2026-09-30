<?php

namespace App\Http\Middleware;

use App\Permissions\PermissionRegistry;
use App\Permissions\PermissionService;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

/**
 * Autorização de todas as rotas do backoffice a partir do catálogo
 * (config/permissions.php). Com permissions.enforce = false só regista no
 * canal "permissions" o que bloquearia — para validar os perfis antes de
 * ligar o bloqueio.
 *
 * O âmbito "próprios" em registos individuais é verificado nos controllers
 * (PermissionService::allows / applyScope); aqui decide-se só a ação.
 */
class AuthorizeResource
{
    public function __construct(private PermissionRegistry $registry, private PermissionService $permissions)
    {
    }

    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();
        $route = $request->route();

        if (!$user || !$route) {
            return $next($request);
        }

        $name = $route->getName() ?? '';
        $rule = $this->registry->ruleForRoute($name, $request->method());

        if ($this->permissions->allowsRule($user, $rule)) {
            return $next($request);
        }

        $enforce = (bool) config('permissions.enforce');

        Log::channel('permissions')->warning($enforce ? 'blocked' : 'would_block', [
            'user_id' => $user->getKey(),
            'user' => trim($user->name . ' ' . $user->last_name),
            'roles' => $user->getRoleNames()->all(),
            'route' => $name,
            'method' => $request->method(),
            'url' => $request->fullUrl(),
            'requires' => $rule->describe(),
            'impersonating' => (bool) $request->session()->get('impersonator_id'),
        ]);

        if ($enforce) {
            abort(403, 'Não tem permissão para esta ação.');
        }

        return $next($request);
    }
}
