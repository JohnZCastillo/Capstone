<?php

namespace App\exception\users;

class UserBlockCooldown extends \Exception
{

    public function __construct(string $message)
    {
        parent::__construct($message);
    }
}