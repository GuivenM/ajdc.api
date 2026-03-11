<?php

namespace App\Http\Middleware;

use Illuminate\Cookie\Middleware\EncryptCookies as Middleware;

class EncryptCookies extends Middleware
{
    /**
     * The names of the cookies that should not be encrypted.
     *
     * @var array<int, string>
     */
    protected $except = [
        // Ajoutez ici les noms des cookies qui ne doivent PAS être encryptés
        // Par exemple, si vous utilisez des cookies pour l'authentification API
        'XSRF-TOKEN',
        'laravel_session',
    ];
}