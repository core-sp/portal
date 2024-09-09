<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Str;

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
            $bloqueia = $service->getService('Suporte')->ipsBloqueados($request->ip());
        }catch(\Exception $e){
            \Log::error('[Erro: '.$e->getMessage().'], [Código: '.$e->getCode().'], [Arquivo: '.$e->getFile().'], [Linha: '.$e->getLine().']');
            return abort(500, 'Erro interno! Tente novamente mais tarde.');
        }

        if(isset($bloqueia)) {
            $texto = Str::limit($bloqueia->ip, 7, '******') . ', bloqueio criado em ' . formataData($bloqueia->updated_at);
            return abort(423, $texto);
        }

        return $next($request);
    }
}
