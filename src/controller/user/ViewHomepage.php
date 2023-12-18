<?php

declare(strict_types=1);

namespace App\controller\user;

use Psr\Http\Message\ResponseInterface as Response;

class ViewHomepage extends AdminAction
{
    /**
     * {@inheritdoc}
     */
    protected function action(): Response
    {
        return $this->view('admin/pages/test.html',[]);
    }
}