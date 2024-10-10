<?php

namespace App\Http\Middleware;

use Illuminate\Auth\Middleware\Authenticate as Middleware;
use App\User;
use Illuminate\Support\Str;

class Authenticate extends Middleware
{
    /**
     * Determine if the user is logged in to any of the given guards.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  array  $guards
     * @return void
     *
     * @throws \Illuminate\Auth\AuthenticationException
     */
    protected function authenticate($request, array $guards)
    {
        if (empty($guards)) {
            $guards = [null];
        }

        foreach ($guards as $guard) {
            if ($this->auth->guard($guard)->check()) {

                if(($this->auth->guard($guard)->user() instanceof User) && (($request->route()->uri() == 'admin') || Str::startsWith($request->route()->uri(), 'admin/'))){
                    $service = resolve('App\Contracts\MediadorServiceInterface');
                    $ativado = $service->getService('Aviso')->existeAtivado() ? '<span class="badge badge-pill badge-warning">Ativo</span>' : '';
                    view()->share('ativado', $ativado);
                }

                return $this->auth->shouldUse($guard);
            }
        }

        $this->unauthenticated($request, $guards);
    }

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
