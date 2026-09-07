<?php

namespace App\Http\Middleware;

use Illuminate\Cookie\Middleware\EncryptCookies as Middleware;

class EncryptCookies extends Middleware
{
    /**
     * The names of the cookies that should not be encrypted.
     *
     * @var array
     */
    protected $except = [
        // Definido em JS puro (document.cookie) em admin-v2.blade.php, para o
        // servidor saber a largura do ecrã sem sniffing de user-agent.
        'viewport',
    ];
}
