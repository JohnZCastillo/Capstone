<?php

namespace App\middleware\access;

class AdminUsers extends AccessControl
{
    function hasAccess(): bool
    {
        return $this->privileges->getAdminUser();
    }

}