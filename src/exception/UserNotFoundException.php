<?php

namespace App\exception;

class UserNotFoundException  extends \Exception {

    public function __construct(string $message)
    {
        parent::__construct($message);
    }
}