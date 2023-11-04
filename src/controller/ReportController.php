<?php

namespace App\controller;

use App\lib\ReportMaker;
use App\lib\Time;

class ReportController extends Controller
{

    public function report($request, $response, $args)
    {

        $params = $request->getParsedBody();
        $fromMonth = $params['from'];
        $toMonth = $params['to'];

        $block = $params['block'] == "ALL" ? null : $params['block'];
        $lot = $params['lot'] == "ALL" ? null : $params['lot'];

        $status = $params['reportStatus'];

        $fromMonth = Time::setToFirstDayOfMonth($fromMonth);
        $toMonth = Time::setToLastDayOfMonth($toMonth);

        $loginUser = $this->getLogin();

        $action = "User with id of " . $loginUser->getId() . " generated a report";

        $this->addActionLog($action);

        $reportMaker = new ReportMaker($loginUser, $fromMonth, $toMonth);

        $users = $this->userSerivce->findUsers($block, $lot);

        $reportData = [];


        switch ($status[0]) {
            case "APPROVED":
                $reportData = $this->paid($users, $params['from'], $params['to']);
                break;
            case "UNPAID":
                $reportData = $this->unpaid($users, $params['from'], $params['to']);
                break;
            case "PENDING":
                $reportData = $this->pending($users, $params['from'], $params['to']);
                break;
            case "REJECTED":
                $reportData = $this->rejected($users, $params['from'], $params['to']);
                break;
        }


        $reportMaker->addBody($reportData[0], $reportData[1], $reportData[2]);

        $response->getBody()->write($reportMaker->output());

        return $response
            ->withHeader('Content-Type', 'application/pdf')
            ->withHeader('Content-Disposition', 'inline; filename="filename.pdf"');

    }

    public function unpaid(array $users, string $from, string $to): array
    {

        $content = array(
            ReportMaker::$UNPAID_HEADER,
        );

        $total = 0;

        foreach ($users as $user) {

            $unpaidData = $this->transactionService->getUnpaid($user,
                $this->duesService,
                $this->getPaymentSettings(),
                Time::setToFirstDayOfMonth($from),
                Time::setToFirstDayOfMonth($to),
            );

            $total = +$unpaidData['total'];

            $unpaids = ReportMaker::unpaid($user, $unpaidData);

            foreach ($unpaids as $unpaid) {
                $content[] = $unpaid;
            }

        }

        $report_data = array(
            "Total Unpaid Due" => [$total],
            "Unpaid Due Breakdown" => $content,
        );

        return array($report_data, [100, 50, 50, 77], "Unpaid Due Breakdown");

    }

    public function paid(array $users, string $from, string $to): array
    {

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

        return array($report_data, [15, 40, 25, 25, 40, 52, 40, 40], "Collected Due Breakdown");
    }

    public function pending(array $users, string $from, string $to): array
    {

        $content = array(
            ReportMaker::$PENDING_HEADER,
        );

        $total = 0;

        foreach ($users as $user) {

            $unpaidData = $this->transactionService->getPendingPayments(
                Time::setToFirstDayOfMonth($from),
                Time::setToFirstDayOfMonth($to),
                $user
            );

            if(count($unpaidData) <= 0){
                continue;
            }

            foreach ($unpaidData as $transaction ){
                $total += $transaction->getAmount();

            }

            $unpaids = ReportMaker::pending($user, $unpaidData);

            foreach ($unpaids as $unpaid) {
                $content[] = $unpaid;
            }

        }

        $report_data = array(
            "Total Pending Due" => [$total],
            "Pending Due Breakdown" => $content,
        );

        return array($report_data, [100, 50, 50, 77], "Pending Due Breakdown");

    }

    public function rejected(array $users, string $from, string $to): array
    {

        $content = array(
            ReportMaker::$REJECTED_HEADER,
        );

        $total = 0;

        foreach ($users as $user) {

            $unpaidData = $this->transactionService->getRejectedPayments(
                Time::setToFirstDayOfMonth($from),
                Time::setToFirstDayOfMonth($to),
                $user,
            );

            foreach ($unpaidData as $transaction ){
                $total += $transaction->getAmount();

            }

            $unpaids = ReportMaker::rejected($user, $unpaidData);

            foreach ($unpaids as $unpaid) {
                $content[] = $unpaid;
            }

        }

        $report_data = array(
            "Total Rejected Due" => [$total],
            "Rejected Due Breakdown" => $content,
        );

        return array($report_data, [20, 50, 30, 50,60,67], "Rejected Due Breakdown");

    }
}