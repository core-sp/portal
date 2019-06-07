<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Queue\SerializesModels;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

class AgendamentoEvent
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $string;

    public function __construct($string)
    {
        $this->string = $string;
    }

    public function broadcastOn()
    {
        return new PrivateChannel('channel-name');
    }
}
