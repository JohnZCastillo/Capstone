<?php

namespace App\controller\user\issues;

use App\controller\user\UserAction;
use Psr\Http\Message\ResponseInterface as Response;

class ViewIssues extends UserAction
{

    protected function action(): Response
    {

        $queryParams = $this->getQueryParams();
        $page = $queryParams['page'];
        $status = empty($queryParams['status']) ? null : $queryParams['status'];
        $createdAt = empty($queryParams['createdAt']) ? null : $queryParams['createdAt'];
        $query = empty($queryParams['query']) ? null : $queryParams['query'];
        $type = empty($queryParams['type']) ? 'POSTED' : $queryParams['type'];


        $max = 5;

        $pagination = null;

        $user = $this->getLoginUser();

        try {
            $pagination = $this->issuesService->getAll($page, $max, $query, $status,$user);
        } catch (\Exception $exception) {
            $this->addErrorMessage('An Internal Error Occurred');
        }

        return $this->view('user/pages/issues.html', [
            'issues' => $pagination->getItems(),
            'status' => $status,
            'paginator' => $pagination,
            'createdAt' => $createdAt,
            'currentPage' => $page,
            'type' => $type,
        ]);

    }
}