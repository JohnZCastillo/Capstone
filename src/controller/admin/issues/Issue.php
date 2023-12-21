<?php

namespace App\controller\admin\issues;

use App\controller\admin\AdminAction;
use App\exception\issue\IssueNotFoundException;
use App\lib\Filter;
use Psr\Http\Message\ResponseInterface as Response;
use Slim\Views\Twig;

class Issue extends AdminAction
{

    /**
     * @inheritDoc
     */
    protected function action(): Response
    {

        $id = $this->args['id'];

        $issue = null;

        try {
            $issue = $this->issuesService->findById($id);
        } catch (IssueNotFoundException $issueNotFoundException) {
            $this->addErrorMessage($issueNotFoundException->getMessage());
            return $this->redirect('/admin/issues');
        } catch (\Exception $exception) {
            $this->addErrorMessage('An Internal Error Occurred');
        }

        return $this->view('admin/pages/issue.html', [
            'issue' => $issue,
        ]);

    }
}