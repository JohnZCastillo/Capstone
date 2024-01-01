<?php

namespace App\middleware\access;

class AdminIssues extends AccessControl
{
    function hasAccess(): bool
    {
        return $this->privileges->getAdminIssues();
    }

}