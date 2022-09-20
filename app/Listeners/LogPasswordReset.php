<?php

namespace App\Listeners;

use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class LogPasswordReset
{
    public function __construct()
    {
        //
    }

    public function handle(PasswordReset $event)
    {
        $ip = "[IP: " . request()->ip() . "] - ";

        if(isset($event->user))
        {
            if($event->user->getTable() == 'users')
                Log::channel('interno')->info($ip . $event->user->nome.' (usuário '.$event->user->idusuario.') resetou a senha com sucesso em "Esqueci a senha" do Painel Administrativo.');

            if($event->user->getTable() == 'representantes')
                Log::channel('externo')->info($ip . 'Usuário com o cpf/cnpj ' .$event->user->cpf_cnpj. ' alterou a senha com sucesso na Área do Representante.');
        }
    }
}
