<?php

namespace App\controller\admin\budget;

use App\controller\admin\AdminAction;
use Doctrine\DBAL\Driver\Exception;
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

            $tally = $this->fundService->getMonthlyTally(1, 2023);

            $bills = $this->billService->getAll($archiveBill);

            $keys = array_keys($tally);
            $values = array_values($tally);

            $data =
                [
                    'funds' => $funds,
                    'keys' => $keys,
                    'values' => $values,
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