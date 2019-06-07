<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Queue\SerializesModels;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

class CrudEvent
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $model;
    public $action;
    public $id;

    public function __construct($model, $action, $id)
    {
        $this->model = $model;
        $this->action = $action;
        $this->id = $id;
    }

    public function broadcastOn()
    {
        return new PrivateChannel('channel-name');
    }
}
