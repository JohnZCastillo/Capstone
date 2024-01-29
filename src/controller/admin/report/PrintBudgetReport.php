<?php

namespace App\controller\admin\report;

use App\controller\admin\AdminAction;
use App\lib\DocxMaker;
use App\lib\NumberFormat;
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

            $key = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];

            $fundId = $this->args['id'];

            $year = Time::getCurrentYear();

            $fund = $this->fundService->findById($fundId);

            $prevCollection = $this->fundService->getCollection($fundId, $year - 1);
            $netIncome = $this->fundService->getCollection($fundId, $year);
            $incomes = $this->fundService->getYearlyIncome($fundId, $year);
            $expenses = $this->fundService->getYearlyExpenses($fundId, $year);

            NumberFormat::formatArray($prevCollection,$key);
            NumberFormat::formatArray($netIncome,$key);
            NumberFormat::formatArray($incomes,$key);
            NumberFormat::formatArray($expenses,$key);

            $prevCollection['TITLE'] = 'Previous Collection';
            $netIncome['TITLE'] = 'Net Income';
            $expenses['TITLE'] = 'Expenses';
            $incomes['TITLE'] = 'Incomes';

            $reportContent = [$prevCollection, $netIncome, $expenses, $incomes];

            $summary['TOTAL'] = $fund->computeTotal();
            $summary['INCOME'] = $fund->computeExpenses();
            $summary['EXPENSE'] = $fund->computeIncomes();
            $summary['HEADLINE'] = $fund->getTitle() . " Report for year $year";

            NumberFormat::format($summary['TOTAL']);
            NumberFormat::format($summary['INCOME']);
            NumberFormat::format($summary['EXPENSE']);

            $docxMaker = new DocxMaker('budget_template.docx');

            $docxMaker->addBody($reportContent, 'TITLE');
            $docxMaker->addHeader($summary);
            $output = $docxMaker->output();

            $pdfResponse = new PdfResponse($output, 'test.pdf');

            $this->addActionLog('Budget Report was created', LogsTag::paymentReport());

            return $pdfResponse->getResponse();

        } catch (\Exception $exception) {
            return  $this->respondWithData(['message' => $exception->getMessage()],500);
        }

    }
}