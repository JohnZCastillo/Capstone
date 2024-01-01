<?php

namespace App\controller\admin\report;

use App\controller\admin\AdminAction;
use App\lib\BudgetReportDocx;
use App\lib\Time;
use NcJoes\OfficeConverter\OfficeConverter;
use Psr\Http\Message\ResponseInterface as Response;

class PrintBudgetReport extends AdminAction
{

    /**
     * @inheritDoc
     */
    protected function action(): Response
    {

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

        $dir = __DIR__ . '/../../../../template/';

        $data = $this->fundService->getYearlyExpenses($fundId, Time::getCurrentYear());

        $target = BudgetReportDocx::generate($reportContent,$summary);

        $converter = new OfficeConverter($target, $dir, 'soffice', false);

        $outputFile = 'output-file.pdf';

        $converter->convertTo($outputFile);

        $response = new \Slim\Psr7\Response();

        $response = $response->withHeader('Content-Type', 'application/pdf');
        $response = $response->withHeader('Content-Disposition', 'attachment; filename="report.pdf"');

        $fileStream = fopen($dir . $outputFile, 'r');
        $response->getBody()->write(fread($fileStream, filesize($dir . $outputFile)));
        fclose($fileStream);

        return $response;

    }
}