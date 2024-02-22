<?php

namespace App\exception\issue;

class IssuesExpiredException  extends \Exception {

    public function __construct(string $message)
    {
        parent::__construct($message);
    }
}