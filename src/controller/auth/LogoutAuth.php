<?php

namespace App\controller\auth;

use App\controller\admin\AdminAction;
use Psr\Http\Message\ResponseInterface as Response;

class LogoutAuth extends AdminAction
{

    /**
     * @inheritDoc
     */
    protected function action(): Response
    {
        $this->loginHistoryService->addLogoutLog();

        session_regenerate_id();

        session_destroy();

        return  $this->redirect('/login');
    }
}