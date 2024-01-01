<?php

namespace App\exception\payment;

class PaymentNotFound  extends \Exception {

    public function __construct(int $id)
    {
        parent::__construct("Payment with id of $id is missing");
    }
}