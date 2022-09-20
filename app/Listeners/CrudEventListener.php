<?php

namespace App\Listeners;

use App\Events\CrudEvent;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class CrudEventListener
{
    public function __construct()
    {
        //
    }

    public function handle(CrudEvent $event)
    {
        $nome = Auth::user()->nome;
        $id = Auth::id();
        Log::channel('interno')->info("[IP: " . request()->ip() . "] - " . $nome.' (usuário '.$id.') '.$event->action.' *'.$event->model.'* (id: '.$event->id.')');
    }
}
