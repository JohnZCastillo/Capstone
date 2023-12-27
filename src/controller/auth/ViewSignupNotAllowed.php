<?php

namespace App\controller\auth;

use App\controller\admin\AdminAction;
use Psr\Http\Message\ResponseInterface as Response;

class ViewSignupNotAllowed extends AdminAction
{

    /**
     * @inheritDoc
     */
    protected function action(): Response
    {
       return  $this->view('temporary.html',[]);
    }
}