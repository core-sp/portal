<?php

namespace App\Listeners;

use App\Events\CrudEvent;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class CrudEventListener
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
     * @param  CrudEvent  $event
     * @return void
     */
    public function handle(CrudEvent $event)
    {
        $nome = Auth::user()->nome;
        $id = Auth::id();
        Log::channel('usuarios')->info($nome.' (usuário '.$id.') '.$event->action.' *'.$event->model.'* (id: '.$event->id.')');
    }
}
