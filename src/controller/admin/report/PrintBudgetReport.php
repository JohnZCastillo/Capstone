<?php

namespace App\controller\admin\report;

use App\controller\admin\AdminAction;
use App\lib\DocxMaker;
use App\lib\PdfResponse;
use App\lib\Time;
use App\model\enum\LogsTag;
use Psr\Http\Message\ResponseInterface as Response;

class PrintBudgetReport extends AdminAction
{

    /**
     * @inheritDoc
     */
    protected function action(): Response
    {

        try {
            $fundId = $this->args['id'];

            $year = Time::getCurrentYear();

            $fund = $this->fundService->findById($fundId);

            $prevCollection = $this->fundService->getCollection($fundId, $year - 1);
            $netIncome = $this->fundService->getCollection($fundId, $year);
            $incomes = $this->fundService->getYearlyIncome($fundId, $year);
            $expenses = $this->fundService->getYearlyExpenses($fundId, $year);

            $prevCollection['TITLE'] = 'Prev Collection';
            $netIncome['TITLE'] = 'Net Income';
            $expenses['TITLE'] = 'Expenses';
            $incomes['TITLE'] = 'Incomes';

            $reportContent = [$prevCollection, $netIncome, $expenses, $incomes];

            $summary['TOTAL'] = $fund->computeTotal();
            $summary['INCOME'] = $fund->computeExpenses();
            $summary['EXPENSE'] = $fund->computeIncomes();

            $docxMaker = new DocxMaker('budget_template.docx');

            $docxMaker->addBody($reportContent, 'TITLE');
            $docxMaker->addHeader($summary);
            $output = $docxMaker->output();

            $pdfResponse = new PdfResponse($output, 'test.pdf');

            $this->addActionLog('Budget Report was created', LogsTag::paymentReport());

            return $pdfResponse->getResponse();

        } catch (\Exception $exception) {
            return  $this->respondWithData(['message' => 'an internal error occurred'],500);
        }

    }
}