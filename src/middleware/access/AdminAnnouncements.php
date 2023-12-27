<?php

namespace App\middleware\access;

class AdminAnnouncements extends AccessControl
{
    function hasAccess(): bool
    {
        return $this->privileges->getAdminAnnouncement();
    }

}