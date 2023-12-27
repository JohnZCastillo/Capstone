<?php

namespace App\middleware\role;

use App\middleware\role\RoleBaseAuth;
use App\model\enum\UserRole;
class AdminAuth extends RoleBaseAuth
{
    function isAllowed(): bool
    {
        return $this->role == UserRole::admin() || $this->role == UserRole::superAdmin();
    }
}