<?php

namespace App\controller\admin\overview;

use App\controller\admin\AdminAction;
use Psr\Http\Message\ResponseInterface as Response;

class ViewOverview extends AdminAction
{

    /**
     * @inheritDoc
     */
    protected function action(): Response
    {

        return $this->view('admin/pages/overview.html',[]);
    }
}