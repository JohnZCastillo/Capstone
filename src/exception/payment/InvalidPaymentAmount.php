<?php

namespace App\exception\payment;

class InvalidPaymentAmount  extends \Exception {

    public function __construct(string $message)
    {
        parent::__construct($message);
    }
}