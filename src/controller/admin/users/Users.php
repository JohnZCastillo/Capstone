<?php

namespace App\controller\admin\users;

use App\controller\admin\AdminAction;
use Psr\Http\Message\ResponseInterface as Response;

class Users extends AdminAction
{

    /**
     * @inheritDoc
     */
    protected function action(): Response
    {

        try{

            $queryParams = $this->getQueryParams();

            $page = $queryParams['page'];
            $block = $queryParams['block'];
            $lot = $queryParams['lot'];

            $role = empty($queryParams['role']) ? 'admin' : $queryParams['role'];

            $max = 10;

            $query = $queryParams['query'];

            $pagination = $this->userService->getAll($page, $max, $query, $role,$block,$lot);

            return $this->view('admin/pages/users.html', [
                'users' => $pagination->getItems(),
                'currentPage' => $page,
                'role' => $role,
                'paginator' => $pagination,
                'superAdmin' => $this->getLoginUser()->getRole() === "super",
                'loginUser' => $this->getLoginUser(),
                'query' => $query,
                'selectedBlock' => $block,
                'selectedLot' => $lot
            ]);

        }catch (\Exception $exception){
            $this->addErrorMessage($exception->getMessage());
        }
    }
}