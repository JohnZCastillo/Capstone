<?php

namespace App\controller\auth;

use App\controller\admin\AdminAction;
use Psr\Http\Message\ResponseInterface as Response;

class ViewRegister extends AdminAction
{

    /**
     * @inheritDoc
     */
    protected function action(): Response
    {
       return  $this->view('pages/register.html',[]);
    }
}