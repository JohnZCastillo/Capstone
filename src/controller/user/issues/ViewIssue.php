<?php

namespace App\controller\user\issues;

use App\controller\user\UserAction;
use App\exception\issue\IssueNotFoundException;
use App\exception\NotAuthorizeException;
use Psr\Http\Message\ResponseInterface as Response;

class ViewIssue extends UserAction
{

    protected function action(): Response
    {

        $id = $this->args['id'];


        try {

            $issue = $this->issuesService->findById($id);

            if($this->getLoginUser()->getId() != $issue->getUser()->getId()){
                throw new NotAuthorizeException('User Not Authorized');
            }

            return $this->view('user/pages/issue.html', [
                'issue' => $issue,
            ]);

        }  catch (IssueNotFoundException $issueNotFoundException) {
            $this->addErrorMessage($issueNotFoundException->getMessage());
        }  catch (NotAuthorizeException $exception) {
            $this->addErrorMessage($exception->getMessage());
             return $this->redirect('/issues');
        }catch (\Exception $exception) {
            $this->addErrorMessage('An Internal Error Occurred');
        }

        return $this->redirect('/issues');

    }
}