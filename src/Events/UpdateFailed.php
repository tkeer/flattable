<?php

namespace Tkeer\Flattable\Events;

class UpdateFailed
{
    public $message;
    public $model;

    public function __construct($model, $message)
    {
        $this->model = $model;
        $this->message = $message;
    }
}
