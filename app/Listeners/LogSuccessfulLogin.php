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
        if(!Auth::guard('representante')->check() && !Auth::guard('pre_representante')->check())
            Log::channel('interno')->info($event->user->nome.' (usuário '.$event->user->idusuario.') conectou-se ao painel de administrador.');
    }
}
