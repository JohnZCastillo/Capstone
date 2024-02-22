<?php

namespace App\controller\admin\issues;

use App\controller\admin\AdminAction;
use Psr\Http\Message\ResponseInterface as Response;

class Issues extends AdminAction
{

    /**
     * @inheritDoc
     */
    protected function action(): Response
    {

        $queryParams = $this->getQueryParams();
        $page = $queryParams['page'];
        $status = empty($queryParams['status']) ? null : $queryParams['status'];
        $createdAt = empty($queryParams['createdAt']) ? null : $queryParams['createdAt'];
        $query = empty($queryParams['query']) ? null : $queryParams['query'];
        $max = 5;

        $pagination = null;

        try {
            $pagination = $this->issuesService->getAll($page, $max, $query,$status);
        } catch (\Exception $exception) {
            $this->addErrorMessage($exception->getMessage());
//            $this->addErrorMessage('An Internal Error Occurred');
        }

        return $this->view('admin/pages/issues.html', [
            'issues' => $pagination->getItems(),
            'status' => $status,
            'paginator' => $pagination,
            'createdAt' => $createdAt,
            'currentPage' => $page,
        ]);

    }
}