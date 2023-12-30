<?php

declare(strict_types=1);

namespace App\controller\user\payments;

use App\controller\user\UserAction;
use App\lib\Filter;
use App\lib\Time;
use Psr\Http\Message\ResponseInterface as Response;

class ViewHomepage extends UserAction
{
    /**
     * {@inheritdoc}
     */
    protected function action(): Response
    {

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
        $paginator = $this->transactionService->getPayments($user,$page, $max, $id,$status, $from, $to);

        // Get balances
        $currentMonth = Time::thisMonth();
        $nextMonth = Time::nextMonth();
//        $currentDue = $this->getBalance($currentMonth);
//        $nextDue = $this->getBalance($nextMonth);
//
//        // Calculate total dues
//        $totalDues = $this->getTotalDues();

        // Prepare data for the view
        $data = [
            'currentMonth' => $currentMonth,
            'nextMonth' => $nextMonth,
//            'currentDue' => Currency::format($currentDue),
//            'nextDue' => Currency::format($nextDue),
//            'unpaid' => Currency::format($totalDues),
            'transactions' => $paginator->getItems(),
            'currentPage' => $page,
            'query' => $id,
            'from' => $from,
            'to' => $to,
            'status' => $status,
//            'settings' => $this->getPaymentSettings(),
            'paginator' => $paginator,
        ];

        return $this->view('user/pages/dues.html',$data);
    }
}