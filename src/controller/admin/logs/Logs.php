<?php

namespace App\controller\admin\logs;

use App\controller\admin\AdminAction;
use Psr\Http\Message\ResponseInterface as Response;

class Logs extends AdminAction
{

    /**
     * @inheritDoc
     */
    protected function action(): Response
    {

        $queryParams = $this->getQueryParams();

        $page = $queryParams['page'];
        $from = $queryParams['from'];
        $to = $queryParams['to'];

        $email = empty($queryParams['email']) ? null : $queryParams['email'];

        $data = [];

        try {

            $user = null;

            if (isset($email)) {
                $user = $this->userService->findByEmail($email);
            }

            $max = 10;

            $result = $this->logsService->getAll($page, $max, null, $from, $to, $user);

            $data = [
                'logs' => $result->getItems(),
                'currentPage' => $page,
                'from' => $from,
                'to' => $to,
                'user' => $user,
                'status' => $queryParams['status'] ?? null,
                'paginator' => $result,
            ];

        } catch (\Exception $exception) {
            $this->addErrorMessage('Internal Error Occurred');
        }
        return $this->view('admin/pages/logs.html', $data);
    }
}