<?php

namespace App\controller\admin\report;

use App\lib\ReportMaker;
use App\lib\Time;
use Psr\Http\Message\ResponseInterface as Response;

class PaidPaymentReport extends PaymentReport
{
    protected function action(): Response
    {
        $users = $this->userService->findUsers($this->block. $this->lot);

        $from = $this->getFormData()['from'];
        $to = $this->getFormData()['to'];

        $content = array(
            ReportMaker::$PAID_HEADER,
        );

        $total = 0;

        $from = Time::setToFirstDayOfMonth($from);
        $to = Time::setToLastDayOfMonth($to);

        foreach ($users as $user) {

            $totalCollection = $this->transactionService->getTotal("APPROVED", $from, $to);
            $paidData = $this->transactionService->getApprovedPayments($from, $to, ['Approved']);

            $total += $totalCollection;

            $paids = ReportMaker::paid($user, $paidData);

            foreach ($paids as $paid) {
                $content[] = $paid;
            }

        }

        $report_data = array(
            "Total Collected Due" => [$total],
            "Collected Due Breakdown" => $content,
        );


        $reportBody = $this->report(array($report_data, [15, 40, 25, 25, 40, 52, 40, 40], "Collected Due Breakdown"));

        $response = new \Slim\Psr7\Response();

        return $response
            ->withHeader('Content-Type', 'application/pdf')
            ->withHeader('Content-Disposition', 'inline; filename="filename.pdf"');
    }

}