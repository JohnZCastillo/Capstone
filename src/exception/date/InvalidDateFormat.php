<?php

namespace App\exception\date;

class InvalidDateFormat extends \Exception {

    public function __construct()
    {
        parent::__construct("Invalid Date Format");
    }
}