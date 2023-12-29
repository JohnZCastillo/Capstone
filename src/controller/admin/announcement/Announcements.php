<?php

namespace App\controller\admin\announcement;

use App\controller\admin\AdminAction;
use Exception;
use Psr\Http\Message\ResponseInterface as Response;

class Announcements extends AdminAction
{

    protected function action(): Response
    {

        $formData = $this->getQueryParams();

        $page = $formData['page'];
        $id = $formData['query'];
        $status = $formData['status'] ?? 'posted';
        $from = $formData['from'];
        $to = $formData['to'];

        $data = [
            'announcements' => null,
            'query' => $id,
            'currentPage' => $page,
            'from' => $from,
            'to' => $to,
            'status' => $status,
            'paginator' => null,
        ];

        try {

            $max = 5;

            $result = $this->announcementService->getAll($page, $max, $id, $from, $to, $status);

            $data['paginator'] = $result['paginator'];
            $data['announcements'] = $result['result'];

        } catch (Exception $exception) {
            $this->addErrorMessage('An Internal Error Occurred');
        }

        return $this->view('admin/pages/announcements.html', $data);
    }
}