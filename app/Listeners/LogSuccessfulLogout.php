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
        if(!Auth::guard('representante')->check())
            Log::channel('interno')->info($event->user->nome.' (usuário '.$event->user->idusuario.') desconectou-se do painel de administrador.');
    }
}
