<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Providers\RouteServiceProvider;
use App\Support\MobilePwa;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles authenticating users for the application and
    | redirecting them to your home screen. The controller uses a trait
    | to conveniently provide its functionality to your applications.
    |
    */

    use AuthenticatesUsers;

    /**
     * Where to redirect users after login.
     *
     * @var string
     */
    protected $redirectTo = RouteServiceProvider::HOME;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest')->except('logout');
    }

    /**
     * Formulário de login. Quando se vem da área mobile (url.intended em
     * /gestao/mobile, ex.: a app do ecrã principal sem sessão), a página leva o
     * manifest e as meta tags da app mobile; caso contrário fica como sempre.
     */
    public function showLoginForm()
    {
        return view('auth.login', [
            'mobilePwa' => MobilePwa::intendedMobileUrl(request()) !== null,
        ]);
    }

    protected function authenticated(Request $request, $user)
    {
        if (!$user->isApproved()) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            throw \Illuminate\Validation\ValidationException::withMessages([
                'email' => $user->isPending()
                    ? 'A sua candidatura ainda está a ser analisada pela administração.'
                    : 'A sua conta não está ativa. Contacte a administração.',
            ]);
        }

        if ($user->roles->isEmpty()) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            throw \Illuminate\Validation\ValidationException::withMessages([
                'email' => 'A sua conta não tem nenhum perfil atribuído. Contacte a administração.',
            ]);
        }

        // O regresso usa url.intended (ex.: /gestao/mobile/leads); só se aceita
        // um URL do próprio site — qualquer outro é descartado e vale o destino normal.
        MobilePwa::forgetUnsafeIntendedUrl($request);

        // Destino mobile para quem não pode lá entrar (angariador puro → 403;
        // sem access_mobile → salto de volta ao desktop): vai direto ao destino normal.
        if (MobilePwa::intendedMobileUrl($request) !== null
            && ($user->isAngariadorOnly() || !$user->access_mobile)) {
            $request->session()->forget('url.intended');
        }

        return null;
    }

    protected function redirectTo()
    {
        session()->flash('success', 'You are logged in!');

        $user = $this->guard()->user();
        if ($user && $user->isAngariadorOnly()) {
            return route('admin.angariador.dashboard');
        }

        return $this->redirectTo;
    }
}
