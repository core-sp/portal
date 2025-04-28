<?php

namespace App\Http\Middleware;

use Illuminate\Auth\Middleware\Authenticate as Middleware;

class Authenticate extends Middleware
{
    /**
     * Get the path the user should be redirected to when they are not authenticated.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return string
     */
    protected function redirectTo($request)
    {
        if (!$request->expectsJson()) 
        {
            if(\Route::is('representante.*'))
                return route('representante.login');

            if(\Route::is('externo.*'))
                return route('externo.login');   
                 
            return route('login');
        }
    }
}
