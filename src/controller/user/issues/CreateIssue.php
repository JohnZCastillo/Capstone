<?php

namespace App\controller\user\issues;

use App\controller\user\UserAction;
use App\exception\issue\IssuesExpiredException;
use App\lib\Time;
use App\model\enum\IssuesStatus;
use App\model\IssuesMessages;
use App\model\IssuesModel;
use Carbon\Carbon;
use Psr\Http\Message\ResponseInterface as Response;

class CreateIssue extends UserAction
{

    protected function action(): Response
    {

        try {

            $user = $this->getLoginUser();

            $formData = $this->getFormData();

            $transaction = $this->transactionService->findById($this->args['transactionId']);

            $now = Carbon::now();
            $updatedAt = Carbon::createFromFormat('Y-m-d', $transaction->getUpdatedAt()->format('Y-m-d'));

            if((int) $updatedAt->diffInDays($now) > 1){
                 throw new IssuesExpiredException("Creation of the issue is not possible as it has been more than 7 days since it been processed.");
            }

            $issue = new IssuesModel();
            $issue->setCreatedAt(Time::timestamp());
            $issue->setStatus(IssuesStatus::pending());
            $issue->setUser($user);
            $issue->setTransaction($transaction);

            $this->issuesService->save($issue);

            $message = new IssuesMessages();
            $message->setMessage($formData['content']);
            $message->setIssue($issue);
            $message->setUser($this->getLoginUser());

            $this->issueMessageService->save($message);

            return $this->redirect('/issues');

        }catch (IssuesExpiredException $exception){
            $this->addErrorMessage($exception->getMessage());
        }catch (\Exception $exception){
            $this->addErrorMessage('An Internal Error Occurred');
        }

        return $this->redirect("/transaction/".$this->args['transactionId']);
    }
}