<?php

declare(strict_types=1);

namespace App\controller\admin;

use App\lib\Filter;
use App\lib\Time;
use Psr\Http\Message\ResponseInterface as Response;
use Slim\Views\Twig;

class Homepage extends AdminAction
{
    /**
     * {@inheritdoc}
     */
    protected function action(): Response
    {

        $queryParams = $this->getQueryParams();

        $settings = $this->paymentService->findById(1);

        // if page is present then set value to page otherwise to 1
        $page = $queryParams['page'] ?? 1;

        $filter = Filter::check($queryParams);

        $id = empty($queryParams['query']) ? null : $queryParams['query'];

        // max transaction per page
        $max = 5;

        //Get Transaction
        $result = $this->transactionService->adminGetAll($page, $max, $id, $filter);

        $startOfPaymentYear = Time::getYearFromStringDate($this->getPaymentSettings()->getStart());

        $dues = $this->getDues($startOfPaymentYear);

        $data = [
            'paymentYear' => Time::getYearSpan((int)$startOfPaymentYear),
            'paymentStart' => $startOfPaymentYear,
            'dues' => $dues ?? null,
            'transactions' => $result->getItems(),
            'currentPage' => $page,
            'query' => $id,
            'from' => $queryParams['from'] ?? null,
            'to' => $queryParams['to'] ?? null,
            'status' => $queryParams['status'] ?? null,
            'settings' => $settings,
            'paginator' => $result,
        ];

        return $this->view('admin/pages/payments.html', $data);
    }
}