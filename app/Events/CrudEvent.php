<?php

namespace App\Events;

use Illuminate\Queue\SerializesModels;
use Illuminate\Foundation\Events\Dispatchable;

class CrudEvent
{
    use Dispatchable, SerializesModels;

    public $model;
    public $action;
    public $id;

    public function __construct($model, $action, $id)
    {
        $this->model = $model;
        $this->action = $action;
        $this->id = $id;
    }
}
