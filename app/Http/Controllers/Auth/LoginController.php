<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Providers\RouteServiceProvider;
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

        return null;
    }

    protected function redirectTo()
    {
        session()->flash('success', 'You are logged in!');

        $user = $this->guard()->user();
        if ($user && $user->hasRole('angariador') && !$user->hasAnyRole(['admin', 'gestor', 'cms'])) {
            return route('admin.angariador.dashboard');
        }

        return $this->redirectTo;
    }
}
