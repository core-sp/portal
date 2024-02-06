<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Queue\SerializesModels;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

class ExternoEvent
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $string;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct($string, $secao = null)
    {
        $temp = isset($secao) ? 'Usuário ' . auth()->guard('representante')->user()->id . ' ("'. auth()->guard('representante')->user()->cpf_cnpj .'") acessou a aba "'.$secao.'"' : '';
        $this->string = "[IP: " . request()->ip() . "] - " . $temp . $string;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return \Illuminate\Broadcasting\Channel|array
     */
    public function broadcastOn()
    {
        return new PrivateChannel('channel-name');
    }
}
