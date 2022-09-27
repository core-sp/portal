<?php

namespace App\Listeners;

use Illuminate\Auth\Events\Login;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class LogSuccessfulLogin
{
    public function __construct()
    {
        //
    }

    public function handle(Login $event)
    {
        $ip = "[IP: " . request()->ip() . "] - ";

        if($event->guard == 'web')
        {
            if(Auth::guard('web')->check())
                Log::channel('interno')->info($ip . $event->user->nome.' (usuário '.$event->user->idusuario.') conectou-se ao Painel Administrativo.');
        }

        if($event->guard == 'representante')
        {
            if(Auth::guard('representante')->check())
                Log::channel('externo')->info($ip . 'Usuário '.$event->user->id.' ("'.$event->user->registro_core.'") conectou-se à Área do Representante.');
        }

        if($event->guard == 'user_externo')
        {
            if(Auth::guard('user_externo')->check())
                Log::channel('externo')->info($ip . 'Usuário '.$event->user->id.' ("'.$event->user->cpf_cnpj.'") conectou-se à Área do Usuário Externo.');
        }
    }
}
