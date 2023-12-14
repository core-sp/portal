<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\View;

class ShareData
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
            $itens_home = $service->getService('HomeImagem')->getItens();
            View::share('itens_home', $itens_home);
        }catch(\Exception $e){
            \Log::error('[Erro: '.$e->getMessage().'], [Código: '.$e->getCode().'], [Arquivo: '.$e->getFile().'], [Linha: '.$e->getLine().']');
            return abort(500, 'Erro interno! Tente novamente mais tarde.');
        }

        return $next($request);
    }
}
