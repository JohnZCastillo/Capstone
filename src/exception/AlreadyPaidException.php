<?php

namespace App\exception;

class AlreadyPaidException  extends \Exception {

    public function __construct(string $month)
    {
        parent::__construct("Already paid for month $month");
    }
}