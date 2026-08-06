<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Providers\RouteServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Password;

/**
 * Página de "primeiro acesso" — o link enviado por email (SetPasswordMail)
 * traz para aqui um utilizador recém-criado para que defina a sua própria
 * password. Reaproveita o password broker do Laravel ("setup", configurado
 * em config/auth.php com validade mais longa que o "esqueci-me da password").
 */
class SetPasswordController extends Controller
{
    public function show(Request $request, string $token)
    {
        return view('auth.passwords.setup', [
            'token' => $token,
            'email' => $request->query('email', ''),
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'token' => 'required',
            'email' => 'required|email',
            'password' => 'required|string|min:6|confirmed',
        ]);

        $user = null;

        $response = Password::broker('setup')->reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function ($resetUser, $password) use (&$user) {
                $resetUser->password = $password;
                $resetUser->save();
                $user = $resetUser;
            }
        );

        if ($response !== Password::PASSWORD_RESET) {
            return back()->withErrors(['email' => __($response)])->withInput($request->only('email'));
        }

        Auth::login($user);

        if ($user->hasRole('angariador') && !$user->hasAnyRole(['admin', 'gestor', 'cms'])) {
            $redirectTo = route('admin.angariador.dashboard');
        } else {
            $redirectTo = RouteServiceProvider::HOME;
        }

        return redirect($redirectTo)->with('success', 'Password definida com sucesso. Bem-vindo!');
    }
}
