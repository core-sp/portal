<?php

namespace App\Listeners;

use App\Events\CriouNoticiaEvent;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

class CriouNoticiaEventListener
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param  CriouNoticiaEvent  $event
     * @return void
     */
    public function handle(CriouNoticiaEvent $event)
    {
        $idusuario = session('idusuario');
        $nomeusuario = session('nome');
        info($nomeusuario.' (usuário: '.$idusuario.') criou uma nova notícia (idnoticia: '.$event->idnoticia.').');
    }
}
