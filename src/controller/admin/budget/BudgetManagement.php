<?php

namespace App\controller\admin\budget;

use App\controller\admin\AdminAction;
use Exception;
use Psr\Http\Message\ResponseInterface as Response;

class BudgetManagement extends AdminAction
{

    /**
     * @inheritDoc
     */
    protected function action(): Response
    {

        $data = [];

        try {

            return $this->redirect('/admin/fund/1');

            $query = $this->getQueryParams();


            $archived = false;
            $archiveBill = false;

            if (isset($query['status'])) {
                $archived = !($query['status'] == 'active');
            }

            if (isset($query['bill'])) {
                $archiveBill = !($query['bill'] == 'active');
            }

            $funds = $this->fundService->getAll($archived);
            $bills = $this->billService->getAll($archiveBill);

            $data =
                [
                    'funds' => $funds,
                    'bills' => $bills,
                    'archived' => $archived,
                    'archiveBill' => $archiveBill,
                ];

        } catch (Exception $exception) {
            $this->addErrorMessage('An Internal Error Occurred');
        }

        return $this->view("admin/pages/budget.html", $data);
    }
}