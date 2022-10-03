<?php

namespace App\Listeners;

use Illuminate\Auth\Events\Logout;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class LogSuccessfulLogout
{
    public function __construct()
    {
        //
    }

    public function handle(Logout $event)
    {
        $ip = "[IP: " . request()->ip() . "] - ";

        if($event->guard == 'web')
        {
            if(Auth::guard('web')->check())
                Log::channel('interno')->info($ip . $event->user->nome.' (usuário '.$event->user->idusuario.') desconectou-se do Painel Administrativo.');
            else
                Log::channel('interno')->info($ip . 'Sessão expirou / não há sessão ativa ao realizar o logout do Painel Administrativo.');
        }

        if($event->guard == 'representante')
        {
            if(Auth::guard('representante')->check())
                Log::channel('externo')->info($ip . 'Usuário '.$event->user->id.' ("'.$event->user->registro_core .'") desconectou-se da Área do Representante.');
            else
                Log::channel('externo')->info($ip . 'Sessão expirou / não há sessão ativa ao realizar o logout da Área do Representante.');
        }

        if($event->guard == 'user_externo')
        {
            if(Auth::guard('user_externo')->check())
                Log::channel('externo')->info($ip . 'Usuário '.$event->user->nome.' ("'.formataCpfCnpj($event->user->cpf_cnpj) .'") desconectou-se da Área do Usuário Externo.');
            else
                Log::channel('externo')->info($ip . 'Sessão expirou / não há sessão ativa ao realizar o logout da Área do Usuário Externo.');
        }
    }
}
