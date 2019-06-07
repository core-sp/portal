<?php

namespace App\Listeners;

use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use App\Events\AgendamentoEvent;
use Illuminate\Support\Facades\Log;

class AgendamentoEventListener
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
     * @param  object  $event
     * @return void
     */
    public function handle(AgendamentoEvent $event)
    {
        Log::channel('externo')->info($event->string);
    }
}
