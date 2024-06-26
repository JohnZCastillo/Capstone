<?php

namespace App\controller\admin\report;

use App\controller\admin\AdminAction;
use App\controller\admin\payments\Transaction;
use App\lib\DocxMaker;
use App\lib\NumberFormat;
use App\lib\PdfResponse;
use App\model\enum\LogsTag;
use App\model\ReceiptModel;
use App\model\TransactionModel;
use App\model\UserModel;
use Carbon\Carbon;
use DateTime;
use Slim\Psr7\Response;

class PaymentReportPreview extends AdminAction
{

    protected  $formData = [];


    protected string $coverage;

    public function action(): Response
    {

        try {
            $formData = $this->getFormData();

            $this->formData = $formData;

            $from = $formData['from'];
            $to = $formData['to'];

            $block = $formData['block'] ?? null;
            $lot = $formData['lot'] ?? null;

            $status = $formData['reportStatus'][0];


            $this->coverage = Carbon::createFromFormat('Y-m', $from)
                    ->format('M Y')
                . ' - ' .
                Carbon::createFromFormat('Y-m', $to)
                    ->format('M Y');

            switch ($status) {
                case 'APPROVED':
                    return $this->approvePaymentReport();
                case 'REJECTED':
                    return $this->rejectedPaymentReport();
                case 'PENDING':
                    return $this->pendingPaymentReport();
                case 'UNPAID':
                    return $this->unpaidPaymentReport();
            }
        } catch (\Exception $exception) {

            $this->addErrorMessage($exception->getMessage());
        }

        return $this->redirect('/admin/payments');
    }

    public function approvePaymentReport(): Response
    {

        $formData = $this->getFormData();

        $from = $formData['from'];
        $to = $formData['to'];

        $block = $formData['block'] ?? null;
        $lot = $formData['lot'] ?? null;

        $transactions = $this->transactionService->getTransactions($from, $to, 'approved', $block, $lot);

        $data = array();

        $totalAmount = 0;

        /** @var  TransactionModel $transaction */
        foreach ($transactions as $transaction) {

            $user = $transaction->getUser();

            $receipts = $transaction->getReceipts();

            $receiptsHolder = '';

            /** @var ReceiptModel $receipt */
            foreach ($receipts as $receipt) {

                $receiptsHolder = $receiptsHolder . ' ' . $receipt->getTransaction()->getPaymentMethod();

                if($receipt->getTransaction()->getPaymentMethod() == 'gcash'){
                    $receiptsHolder = $receiptsHolder . ' (' . $receipt->getReferenceNumber() . ')';
                }
            }

            $fromCoverage = $transaction->getFromMonth();
            $toCoverage = $transaction->getToMonth();

            $coverage = $fromCoverage->format('M Y') . ' - ' . $toCoverage->format('M Y');

            $amount = $transaction->getAmount();

            $totalAmount += $amount;

            NumberFormat::format($amount);

            $data[] = array(
                'ID' => 'CH' . $transaction->getUser()->getBlock() .  $transaction->getUser()->getLot() ,
                'UNIT' => 'B' . $user->getBlock() . ' L' . $user->getLot(),
                'USER' => $this->areaService->getOwner($transaction->getUser()),
                'AMOUNT' => $amount,
                'REFERENCE' => $receiptsHolder,
                'COVERAGE' => $coverage,
                'CREATED' => $transaction->getCreatedAt()->format('Y-m-d'),
            );
        }

        return  $this->view('/admin/pages/approve-preview.html', [
            'data' => $data,
            'header' => [
                'TITLE' => 'APPROVED PAYMENTS REPORT',
                'TOTAL' => $totalAmount,
                'REPORT_COVERAGE' => $this->coverage,
            ]
            ,'form' => $this->formData

        ]);

    }

    protected function rejectedPaymentReport(): Response
    {

        $formData = $this->getFormData();

        $from = $formData['from'];
        $to = $formData['to'];

        $block = $formData['block'] ?? null;
        $lot = $formData['lot'] ?? null;

        $transactions = $this->transactionService->getTransactions($from, $to, 'rejected', $block, $lot);

        $data = array();

        $totalAmount = 0;

        foreach ($transactions as $transaction) {

            /** @var TransactionModel $transaction */

            $user = $transaction->getUser();

            $receipts = $transaction->getReceipts();

            $fromCoverage = $transaction->getFromMonth();
            $toCoverage = $transaction->getToMonth();

            $coverage = $fromCoverage->format('M Y') . ' - ' . $toCoverage->format('M Y');

            $amount = $transaction->getAmount();
            $totalAmount += $amount;

            NumberFormat::format($amount);

            $data[] = array(
                'ID' => 'CH' . $transaction->getUser()->getBlock() .  $transaction->getUser()->getLot() ,
                'UNIT' => 'B' . $user->getBlock() . ' L' . $user->getLot(),
                'USER' => $this->areaService->getOwner($transaction->getUser()),
                'AMOUNT' => $amount,
                'COVERAGE' => $coverage,
                'REJECTOR' => $transaction->getProcessBy()->getName(),
            );
        }

        return  $this->view('/admin/pages/rejected-preview.html', [
            'data' => $data,
            'header' => [
                'TITLE' => 'REJECTED PAYMENT REPORT',
                'TOTAL' => $totalAmount,
                'REPORT_COVERAGE' => $this->coverage,
            ]
            ,'form' => $this->formData

        ]);
    }

    public function pendingPaymentReport(): Response
    {

        $formData = $this->getFormData();

        $from = $formData['from'];
        $to = $formData['to'];

        $block = $formData['block'] ?? null;
        $lot = $formData['lot'] ?? null;

        $transactions = $this->transactionService->getTransactions($from, $to, 'pending', $block, $lot);

        $data = array();

        $totalAmount = 0;

        foreach ($transactions as $transaction) {

            $user = $transaction->getUser();

            $receipts = $transaction->getReceipts();

            $receiptsHolder = '';

            foreach ($receipts as $receipt) {
                $receiptsHolder = $receiptsHolder . ' ' . $receipt->getReferenceNumber();
            }

            $fromCoverage = $transaction->getFromMonth();
            $toCoverage = $transaction->getToMonth();

            $coverage = $fromCoverage->format('M Y') . ' - ' . $toCoverage->format('M Y');

            $amount = $transaction->getAmount();
            $totalAmount += $amount;

            NumberFormat::format($amount);

            $data[] = array(
                'ID' => 'CH' . $transaction->getUser()->getBlock() .  $transaction->getUser()->getLot() ,
                'UNIT' => 'B' . $user->getBlock() . ' L' . $user->getLot(),
                'USER' => $this->areaService->getOwner($transaction->getUser()),
                'AMOUNT' => $amount,
                'REFERENCE' => $receiptsHolder,
                'COVERAGE' => $coverage,
                'CREATED' => $transaction->getCreatedAt()->format('Y-m-d'),
            );

        }

        return  $this->view('/admin/pages/pending-preview.html', [
            'data' => $data,
            'header' => [
                'TITLE' => 'PENDING PAYMENTS REPORT',
                'TOTAL' => $totalAmount,
                'REPORT_COVERAGE' => $this->coverage,
            ]
            ,'form' => $this->formData

        ]);
    }

    public function unpaidPaymentReport(): Response
    {

        $formData = $this->getFormData();

        $from = $formData['from'];
        $to = $formData['to'];

        $block = $formData['block'] ?? null;
        $lot = $formData['lot'] ?? null;

        $carbonStart = Carbon::createFromFormat('Y-m', $from);
        $carbonStart->setDay(1);

        $carbonEnd = Carbon::createFromFormat('Y-m', $to);
        $carbonEnd->setDay(1);

        $areas = $this->areaService->getArea($block, $lot);

        $users = [];

        $totalUnpaidDues = 0;

        foreach ($areas as $area) {

            $user = new UserModel();
            $user->setBlock($area->getBlock());
            $user->setLot($area->getLot());

            $users[] = $user;

        }

        $data = [];

        foreach ($users as $user) {

            $total = $this->transactionService->getUnpaid(
                $user,
                $this->duesService,
                $this->paymentService->findById(1),
                $carbonStart->format('Y-m-d'),
                $carbonEnd->format('Y-m-d'),
            )['total'];

            $copy = $total;

            NumberFormat::format($copy);

            $data[] = [
                'AMOUNT' => $copy,
                'UNIT' => 'B' . $user->getBlock() . ' L' . $user->getLot()
            ];

            $totalUnpaidDues += $total;
        }

        return  $this->view('/admin/pages/unpaid-preview.html', [
            'data' => $data,
            'header' => [
                'TITLE' => 'UNPAID PAYMENTS REPORT',
                'TOTAL' => $totalUnpaidDues,
                'REPORT_COVERAGE' => $this->coverage,
            ]
            ,'form' => $this->formData

        ]);
    }

}