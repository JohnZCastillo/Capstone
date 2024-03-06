<?php

namespace App\controller\api\issue;

use App\controller\admin\AdminAction;
use Psr\Http\Message\ResponseInterface as Response;

class IssueMessage extends AdminAction
{

    /**
     * @inheritDoc
     */
    protected function action(): Response
    {

        $issue = $this->issuesService->findById($this->args['id']);

        $user = $this->getLoginUser();

        return  $this->view('user/partials/messages.html',
            [
                'issue'=>$issue,
                'user' => $user,
            ]);

    }
}