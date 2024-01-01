<?php

namespace App\exception;

class NotAuthorizeException extends \Exception
{

    public function __construct(string $message)
    {
        parent::__construct($message);
    }
}