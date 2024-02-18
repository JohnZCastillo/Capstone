<?php

declare(strict_types=1);

namespace App\controller\user\payments;

use App\controller\user\UserAction;
use App\lib\Time;
use Exception;
use Psr\Http\Message\ResponseInterface as Response;

class ViewHomepage extends UserAction
{
    /**
     * {@inheritdoc}
     */
    protected function action(): Response
    {

        $data = [];

        try {

            // Get the user
            $user = $this->getLoginUser();

            $queryParams = $this->getQueryParams();

            $page = $queryParams['page'];
            $id = $queryParams['query'];
            $status = $queryParams['status'];
            $from = $queryParams['from'];
            $to = $queryParams['to'];

            $max = 4;

            // Get transactions
            $paginator = $this->transactionService->getUserPayments($user, $page, $max, $id, $status, $from, $to);

            // Get balances
            $currentMonth = Time::thisMonth();
            $nextMonth = Time::nextMonth();

            $currentDue = $this->transactionService->getBalance($user, $currentMonth, $this->duesService);
            $nextDue = $this->transactionService->getBalance($user, $nextMonth, $this->duesService);

            $settings = $this->paymentService->findById(1);

            $totalDues = $this->transactionService->getUnpaid($user, $this->duesService, $settings)['total'];

            $data = [
                'currentMonth' => $currentMonth,
                'nextMonth' => $nextMonth,
                'currentDue' => $currentDue,
                'nextDue' => $nextDue,
                'unpaid' => $totalDues,
                'transactions' => $paginator->getItems(),
                'currentPage' => $page,
                'query' => $id,
                'from' => $from,
                'to' => $to,
                'status' => $status,
                'settings' => $settings,
                'paginator' => $paginator,
            ];


        } catch (Exception $ex) {
            $this->addErrorMessage($ex->getMessage());
        }

        return $this->view('user/pages/dues.html', $data);

    }
}