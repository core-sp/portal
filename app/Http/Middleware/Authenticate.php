<?php

namespace App\Http\Middleware;

use Illuminate\Auth\Middleware\Authenticate as Middleware;

class Authenticate extends Middleware
{
    // protected function authenticate($request, array $guards)
    // {
    //     // Verificando se o usuário possui as permissões na sessão, em caso negativo, logout é realizado e relogin será necessário
    //     if (empty($guards) && is_null(session('permissoes'))) {
    //         $this->auth->guard(null)->logout();
    //         session()->flush();
    //     }

    //     return parent::authenticate($request, $guards);
    // }

    /**
     * Get the path the user should be redirected to when they are not authenticated.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return string
     */
    protected function redirectTo($request)
    {
        if (!$request->expectsJson()) {
            return \Route::is('representante.*') ? route('representante.login') : route('login');
        }
    }
}
