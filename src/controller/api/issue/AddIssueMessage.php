<?php

namespace App\controller\api\issue;

use App\controller\admin\AdminAction;
use App\lib\Image;
use App\model\enum\IssuesStatus;
use App\model\IssuesMessages;
use Psr\Http\Message\ResponseInterface as Response;

class AddIssueMessage extends AdminAction
{
    protected function action(): Response
    {
        try {

            $issue = $this->issuesService->findById($this->args['id']);

            if($issue->getStatus() == IssuesStatus::PENDING) {
                $message = new IssuesMessages();
                $message->setMessage($this->getFormData()['message']);
                $message->setIssue($issue);
                $message->setImage(false);
                $message->setUser($this->getLoginUser());

                $this->issueMessageService->save($message);
                return $this->respondWithData(['message' => $this->getFormData()]);

            }

            return $this->respondWithData(['message' => []]);

        }catch (\Exception $exception){
            return $this->respondWithData(['message' => $exception->getMessage()],400);

        }

    }
}