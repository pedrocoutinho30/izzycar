<?php

namespace App\Http\Middleware;

use App\Providers\RouteServiceProvider;
use App\Support\MobilePwa;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class RedirectIfAuthenticated
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string ...$guards): Response
    {
        $guards = empty($guards) ? [null] : $guards;

        foreach ($guards as $guard) {
            if (Auth::guard($guard)->check()) {
                // App mobile (PWA) que caiu no login já com sessão: se o destino
                // pendente é da área mobile e o utilizador tem access_mobile,
                // regressa lá em vez de ir para o dashboard desktop. Todos os
                // outros casos ficam como sempre (HOME).
                $mobileUrl = MobilePwa::intendedMobileUrl($request);
                if ($mobileUrl !== null && Auth::guard($guard)->user()?->access_mobile) {
                    $request->session()->forget('url.intended');

                    return redirect()->to($mobileUrl);
                }

                return redirect(RouteServiceProvider::HOME);
            }
        }

        return $next($request);
    }
}
