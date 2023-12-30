<?php

namespace App\controller\user\issues;

use App\controller\user\UserAction;
use App\lib\Time;
use App\model\enum\IssuesStatus;
use App\model\IssuesModel;
use Psr\Http\Message\ResponseInterface as Response;

class CreateIssue extends UserAction
{

    protected function action(): Response
    {

        try {
            $user = $this->getLoginUser();

            $formData = $this->getFormData();

            $issue = new IssuesModel();
            $issue->setTitle($formData['title']);
            $issue->setContent($formData['content']);
            $issue->setCreatedAt(Time::timestamp());
            $issue->setStatus(IssuesStatus::pending());
            $issue->setAction('None');
            $issue->setUser($user);
            $issue->setType('posted');
            $issue->setTarget($formData['target']);

            $this->issuesService->save($issue);

        }catch (\Exception $exception){
            $this->addErrorMessage('An Internal Error Occurred');
        }

        return $this->redirect('/issues');

    }
}