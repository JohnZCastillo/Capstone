<?php

namespace App\controller\auth;

use App\controller\admin\AdminAction;
use Psr\Http\Message\ResponseInterface as Response;

class ViewForgotPassword extends AdminAction
{

    /**
     * @inheritDoc
     */
    protected function action(): Response
    {
       return  $this->view('pages/forgotten-password.html',[]);
    }
}