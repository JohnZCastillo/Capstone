<?php

namespace App\exception\payment;

class InvalidReference  extends \Exception {

    public function __construct()
    {
        parent::__construct("Invalid Reference");
    }
}