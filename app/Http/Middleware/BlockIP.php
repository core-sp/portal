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
        try{
            $service = resolve('App\Contracts\MediadorServiceInterface');
            $ips = $service->getService('Suporte')->ipsBloqueados()->pluck('ip')->all();
    
            if(in_array($request->ip(), $ips)) {
                return abort(423);
            }
        }catch(\Exception $e){
            \Log::error('[Erro: '.$e->getMessage().'], [Código: '.$e->getCode().'], [Arquivo: '.$e->getFile().'], [Linha: '.$e->getLine().']');
            abort(500, 'Erro interno! Tente novamente mais tarde.');
        }

        return $next($request);
    }
}
