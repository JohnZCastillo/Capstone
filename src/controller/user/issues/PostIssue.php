<?php

namespace App\controller\user\issues;

use App\controller\user\UserAction;
use App\exception\issue\IssueNotFoundException;
use App\exception\NotAuthorizeException;
use App\model\enum\AnnouncementStatus;
use Psr\Http\Message\ResponseInterface as Response;

class PostIssue extends UserAction
{

    protected function action(): Response
    {

        $id = $this->args['id'];

        try {

            $issue = $this->issuesService->findById($id);

            if($issue->getUser()->getId() !== $this->getLoginUser()->getId()){
                throw new NotAuthorizeException('You`re not allowed make this action');
            }

            $issue->setType('posted');

            $this->issuesService->save($issue);

        }  catch (NotAuthorizeException $notAuthorizeException) {
            $this->addErrorMessage($notAuthorizeException->getMessage());
        }catch (IssueNotFoundException $issueNotFoundException) {
            $this->addErrorMessage($issueNotFoundException->getMessage());
        }catch (\Exception $exception) {
            $this->addErrorMessage('An Internal Error Occurred');
        }

        return $this->redirect('/issues?type=ARCHIVED');

    }
}