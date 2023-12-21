<?php

namespace App\controller\admin\issues;

use App\controller\admin\AdminAction;
use App\lib\Filter;
use Psr\Http\Message\ResponseInterface as Response;
use Slim\Views\Twig;

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
            $pagination = $this->issuesService->getAll($page, $max, $query, null, $status,);
        } catch (\Exception $exception) {
            $this->addErrorMessage('An Internal Error Occurred');
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