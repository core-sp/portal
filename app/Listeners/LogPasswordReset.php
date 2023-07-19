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

            if(in_array($event->user->getTable(), ['users_externo', 'contabeis']))
            {
                $campo = $event->user->getTable() == 'contabeis' ? $event->user->cnpj : $event->user->cpf_cnpj;
                $tipo = $event->user->getTable() == 'contabeis' ? 'a Contabilidade' : 'o Usuário Externo';
                Log::channel('externo')->info($ip . 'Usuário com o cpf/cnpj ' .$campo. ' alterou a senha com sucesso na Área d'.$tipo.' através do "Esqueci a senha".');
            }
        }
    }
}
