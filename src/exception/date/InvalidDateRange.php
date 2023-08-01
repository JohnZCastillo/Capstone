<?php

namespace App\exception\date;

class InvalidDateRange extends \Exception {

    public function __construct()
    {
        parent::__construct(" Given dates are invalid range");
    }
}