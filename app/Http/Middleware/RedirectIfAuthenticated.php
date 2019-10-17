<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;

class RedirectIfAuthenticated
{
    public function handle($request, Closure $next, $guard = null)
    {
        if (Auth::guard($guard)->check()) {
            switch ($guard) {
                case 'representante':
                    return redirect()->route('representante.dashboard');
                break;
                
                default:
                    return redirect('/home');
                break;
            }
        }

        return $next($request);
    }
}
