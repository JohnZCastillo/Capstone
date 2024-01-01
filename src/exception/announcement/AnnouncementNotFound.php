<?php

namespace App\exception\announcement;

class AnnouncementNotFound extends \Exception
{

    public function __construct(string $message)
    {
        parent::__construct($message);
    }
}