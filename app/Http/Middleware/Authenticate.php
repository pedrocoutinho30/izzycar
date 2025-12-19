<?php

namespace App\Http\Middleware;

use Illuminate\Auth\Middleware\Authenticate as Middleware;
use Illuminate\Http\Request;

class Authenticate extends Middleware
{
    /**
     * Get the path the user should be redirected to when they are not authenticated.
     */
    protected function redirectTo(Request $request): ?string
    {
        // if (app()->environment(['local', 'devop'])) {
        //     auth()->loginUsingId(
        //     \App\Models\User::where('email', 'izzycarpt@gmail.com')->first()->id
        //     );
        // }

       
        return $request->expectsJson() ? null : route('login');
    }
}
