<?php

namespace App\controller\api\issue;

use App\controller\admin\AdminAction;
use App\exception\issue\IssueNotFoundException;
use App\model\enum\IssuesStatus;
use Doctrine\DBAL\Driver\Exception;
use Psr\Http\Message\ResponseInterface as Response;

class ResolveIssue extends AdminAction
{

    /**
     * @inheritDoc
     */
    protected function action(): Response
    {

        try {

            $issue = $this->issuesService->findById($this->args['id']);

            $issue->setStatus(IssuesStatus::RESOLVED);

            $this->issuesService->save($issue);

        } catch (IssueNotFoundException $e) {
            $this->addErrorMessage('Issue not found');
            return $this->redirect('/admin/issues');
        } catch (Exception $e) {
            $this->addErrorMessage('An Internal Error Occurred');
        }

        return $this->redirect('/admin/issue/' .  $this->args['id'],302);

    }
}