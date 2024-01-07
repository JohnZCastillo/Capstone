<?php

namespace App\controller\admin\logs;

use App\controller\admin\AdminAction;
use App\model\enum\LogsTag;
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
        $selectedTag = empty($queryParams['tag']) ? null : $queryParams['tag'];

        $data = [];

        $staffs = $this->userService->getStaffs();

        $tags = LogsTag::getValues();

        try {

            $user = null;
            $tag = $selectedTag;

            if (isset($email) && $email !== 'ALL') {
                $user = $this->userService->findByEmail($email);
            }

            if ($selectedTag === 'ALL') {
                $tag = null;
            }

            $max = 10;

            $result = $this->logsService->getAll($page, $max, $tag, $from, $to, $user);

            $data = [
                'logs' => $result->getItems(),
                'currentPage' => $page,
                'from' => $from,
                'to' => $to,
                'user' => $user,
                'status' => $queryParams['status'] ?? null,
                'paginator' => $result,
                'staffs' => $staffs,
                'tags' => $tags,
                'selectedTag' => $selectedTag
            ];

        } catch (\Exception $exception) {
//            $this->addErrorMessage('Internal Error Occurred');
            $this->addErrorMessage($exception->getMessage());
        }

        return $this->view('admin/pages/logs.html', $data);
    }
}