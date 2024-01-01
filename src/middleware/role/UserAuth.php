<?php

namespace App\middleware\role;

use App\middleware\role\RoleBaseAuth;
use App\model\enum\UserRole;
class UserAuth extends RoleBaseAuth
{
    function isAllowed(): bool
    {
        return $this->role == UserRole::user();
    }
}