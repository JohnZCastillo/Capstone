<?php

declare(strict_types=1);

namespace App\controller\user;

use App\controller\Action;
use App\service\UserService;
use Psr\Log\LoggerInterface;

abstract class UserAction extends Action
{
    protected UserService $userService;

    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
    }

}