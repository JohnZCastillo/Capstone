<?php

namespace App\controller\admin\users;

use App\controller\admin\AdminAction;
use App\lib\Login;
use Psr\Http\Message\ResponseInterface as Response;

class Test extends AdminAction
{

    /**
     * @inheritDoc
     */
    protected function action(): Response
    {
        return $this->respondWithData(['session_id' => Login::getLogin(),
            'is_login' => Login::isLogin()]);
    }
}