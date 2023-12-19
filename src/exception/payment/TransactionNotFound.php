<?php

namespace App\exception\payment;

class TransactionNotFound  extends \Exception {

    public function __construct(int $id)
    {
        parent::__construct("Transaction with id of $id is missing");
    }
}