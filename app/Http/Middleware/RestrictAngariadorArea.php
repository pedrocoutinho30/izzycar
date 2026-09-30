<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

/**
 * Um utilizador cujo único papel é "angariador" só pode aceder às rotas da
 * sua própria área (admin.angariador.*) — todo o resto do backoffice fica
 * bloqueado, mesmo que a rota exista e não tenha outra proteção própria.
 *
 * Também se aplica durante impersonation (o admin a "ver como" um angariador
 * fica sujeito às mesmas regras, tal como o próprio angariador veria).
 *
 * Um utilizador sem nenhum perfil não tem acesso ao backoffice.
 */
class RestrictAngariadorArea
{
    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();

        if ($user && $user->roles->isEmpty()) {
            abort(403, 'A sua conta não tem nenhum perfil atribuído. Contacte a administração.');
        }

        if (!$user || !$user->isAngariadorOnly()) {
            return $next($request);
        }

        $routeName = $request->route()?->getName() ?? '';

        if (!str_starts_with($routeName, 'admin.angariador.')) {
            abort(403, 'Sem acesso a esta área.');
        }

        return $next($request);
    }
}
