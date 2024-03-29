<?php

namespace App\Listeners;

use Illuminate\Auth\Events\Failed;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class LogFailedLogin
{
    public function __construct()
    {
        //
    }

    public function handle(Failed $event)
    {
        $ip = "[IP: " . request()->ip() . "] - ";

        if($event->guard == 'web')
        {
            if(isset($event->user))
                Log::channel('interno')->info($ip . $event->user->nome.' (usuário '.$event->user->idusuario.') não conseguiu logar no Painel Administrativo.');
            else
                Log::channel('interno')->info($ip . 'Usuário não encontrado com o username "'.request()->login.'" não conseguiu logar no Painel Administrativo.');
        }

        if($event->guard == 'representante')
        {
            if(isset($event->user))
                Log::channel('externo')->info($ip . 'Usuário com o cpf/cnpj ' .$event->user->cpf_cnpj. ' não conseguiu logar na Área do Representante.');
            else
                Log::channel('externo')->info($ip . 'Usuário não encontrado com o cpf/cnpj "'.request()->cpf_cnpj.'" não conseguiu logar na Área do Representante.');
        }
    }
}
