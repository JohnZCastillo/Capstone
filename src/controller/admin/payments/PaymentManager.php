<?php

namespace App\controller\admin\payments;

use App\controller\admin\AdminAction;
use Psr\Http\Message\ResponseInterface as Response;

abstract class PaymentManager extends AdminAction {

    protected function action(): Response
    {
        return  $this->process();
    }

    protected abstract function process ():Response;

}