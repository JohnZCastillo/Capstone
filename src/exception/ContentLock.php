<?php

namespace App\exception;

class ContentLock  extends \Exception {

    public function __construct(string $message)
    {
        parent::__construct($message);
    }
}