<?php

namespace App\exception\payment;

class PaymentReversibilityExpired  extends \Exception {

    public function __construct(string $message)
    {
        parent::__construct($message);
    }
}