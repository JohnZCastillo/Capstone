<?php

namespace App\middleware\access;

class AdminPayments extends AccessControl
{
    function hasAccess(): bool
    {
        return $this->privileges->getAdminPayment();
    }

}