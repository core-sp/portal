<?php

namespace App\Listeners;

use Illuminate\Auth\Events\Lockout;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class LogLockout
{
    public function __construct()
    {
        //
    }

    public function handle(Lockout $event)
    {
        $ip = "[IP: " . request()->ip() . "] - ";

        if($event->request->route()->uri == 'admin/login')
            Log::channel('interno')->info($ip . 'Usuário com username "'.$event->request->login.'" foi bloqueado temporariamente por alguns segundos devido a alcançar o limite de tentativas de login no Painel Administrativo.');

        if($event->request->route()->uri == 'representante/login')
            Log::channel('externo')->info($ip . 'Usuário com cpf/cnpj "'.$event->request->cpf_cnpj.'" foi bloqueado temporariamente por alguns segundos devido a alcançar o limite de tentativas de login na Área do Representante.');

        if($event->request->route()->uri == 'externo/login')
            Log::channel('externo')->info($ip . 'Usuário com cpf/cnpj "'.$event->request->cpf_cnpj.'" foi bloqueado temporariamente por alguns segundos devido a alcançar o limite de tentativas de login na Área do Usuário Externo.');
    }
}
