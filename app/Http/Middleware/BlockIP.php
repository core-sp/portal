<?php

namespace App\Http\Middleware;

use Closure;

class BlockIP
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $service = resolve('App\Contracts\MediadorServiceInterface');
        $ips = $service->getService('Suporte')->ipsBloqueados()->pluck('ip')->all();

        if(in_array($request->ip(), $ips)) {
            return abort(423);
        }

        return $next($request);
    }
}
