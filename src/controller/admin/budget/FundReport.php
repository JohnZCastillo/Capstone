<?php

namespace App\controller\admin\budget;

use App\controller\admin\AdminAction;
use Doctrine\DBAL\Driver\Exception;
use Psr\Http\Message\ResponseInterface as Response;

class FundReport extends AdminAction
{

    /**
     * @inheritDoc
     */
    protected function action(): Response
    {

        $fundId = $this->args['id'];

        $prevCollection = $this->fundService->getCollection($fundId,2022);
        $netIncome = $this->fundService->getCollection($fundId,2023);
        $incomes = $this->fundService->getYearlyIncome($fundId,2023);
        $expenses = $this->fundService->getYearlyExpenses($fundId,2023);

        $header = [
            'Prev' => [
                'title' => 'Prev',
                'data' => $prevCollection
            ],
            'Incomes' => [
                'title' => 'Incomes',
                'data' => $incomes
            ],
            'Expenses' => [
                'title' => 'Expenses',
                'data' => $expenses
            ],
            'NetIncome' => [
                'title' => 'Net Income',
                'data' => $netIncome
            ],
        ];

        return $this->view("admin/pages/fund-report.html", ['header' => $header]);
    }
}