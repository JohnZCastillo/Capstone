<?php

namespace App\exception;

class NotUniqueReferenceException  extends \Exception {

    public function __construct(string $reference)
    {
        parent::__construct("Receipt with reference number $reference was already used");
    }
}