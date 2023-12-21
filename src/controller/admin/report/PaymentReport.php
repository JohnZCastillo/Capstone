<?php

namespace App\controller\admin\report;

use App\controller\admin\AdminAction;
use App\exception\date\InvalidDateFormat;
use App\lib\ReportMaker;
use App\lib\Time;
use phpDocumentor\Reflection\Types\Parent_;
use PHPUnit\Exception;

abstract class PaymentReport extends  AdminAction
{

    protected string $block;
    protected string $lot;

    public function __construct()
    {
        $formData = $this->getFormData();
        $this->block = $formData['block'] == "ALL" ? null : $formData['block'];
        $this->lot = $formData['lot'] == "ALL" ? null : $formData['lot'];
    }


    /**
     * @throws Exception
     * @throws InvalidDateFormat
     */
    public function report(array $data): string
    {
        $formData = $this->getFormData();

        $fromMonth = $formData['from'];
        $toMonth = $formData['to'];

        $fromMonth = Time::setToFirstDayOfMonth($fromMonth);
        $toMonth = Time::setToLastDayOfMonth($toMonth);

        $loginUser = $this->getLoginUser();

        $action = "User with id of " . $loginUser->getId() . " generated a report";

        $this->addActionLog($action,'payment report');

        $reportMaker = new ReportMaker($loginUser, $fromMonth, $toMonth);

        $reportMaker->addBody($data[0], $data[1], $data[2]);

        return $reportMaker->output();
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