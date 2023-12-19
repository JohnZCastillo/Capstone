<?php

namespace App\exception\payment;

class PaymentSettingsNotFound  extends \Exception {

    public function __construct(int $id)
    {
        parent::__construct("Payment settings with id of $id is missing");
    }
}