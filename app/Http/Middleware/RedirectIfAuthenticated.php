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

                case 'user_externo':
                case 'contabil':
                    return redirect()->route('externo.dashboard');
                break;
                
                default:
                    return redirect('/admin');
                break;
            }
        }

        return $next($request);
    }
}
