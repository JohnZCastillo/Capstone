<?php

namespace App\controller\admin\report;

use App\controller\admin\AdminAction;
use App\controller\admin\payments\Transaction;
use App\lib\DocxMaker;
use App\lib\NumberFormat;
use App\lib\PdfResponse;
use App\model\enum\LogsTag;
use App\model\TransactionModel;
use App\model\UserModel;
use Carbon\Carbon;
use DateTime;
use Slim\Psr7\Response;

class PaymentReport extends AdminAction
{


    protected string $coverage;

    public function action(): Response
    {

        try {
            $formData = $this->getFormData();

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

        foreach ($transactions as $transaction) {

            $user = $transaction->getUser();

            $receipts = $transaction->getReceipts();

            $receiptsHolder = '';

            foreach ($receipts as $receipt) {
                $receiptsHolder = $receiptsHolder . ' ' . $receipt->getReferenceNumber();
            }

            $fromCoverage = new DateTime($transaction->getFromMonth());
            $toCoverage = new DateTime($transaction->getToMonth());

            $coverage = $fromCoverage->format('M Y') . ' - ' . $toCoverage->format('M Y');

            $amount = $transaction->getAmount();
            $totalAmount += $amount;

            NumberFormat::format($amount);

            $data[] = array(
                'ID' => $transaction->getId(),
                'UNIT' => 'B' . $user->getBlock() . ' L' . $user->getLot(),
                'AMOUNT' => $amount,
                'REFERENCE' => $receiptsHolder,
                'COVERAGE' => $coverage,
                'CREATED' => $transaction->getCreatedAt()->format('Y-m-d'),
            );


        }

        $docxMaker = new DocxMaker('approve_payment.docx');

        NumberFormat::format($totalAmount);

        $docxMaker->addBody($data, 'ID');
        $docxMaker->addHeader([
            'TITLE' => 'APPROVED PAYMENTS REPORT',
            'TOTAL' => $totalAmount,
            'REPORT_COVERAGE' => $this->coverage,
        ]);

        $output = $docxMaker->output();

        $pdfResponse = new PdfResponse($output, 'test.pdf');

        $this->addActionLog('Approved Payment Report was created', LogsTag::paymentReport());


        return $pdfResponse->getResponse();
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

            $fromCoverage = new DateTime($transaction->getFromMonth());
            $toCoverage = new DateTime($transaction->getToMonth());

            $coverage = $fromCoverage->format('M Y') . ' - ' . $toCoverage->format('M Y');

            $amount = $transaction->getAmount();
            $totalAmount += $amount;

            NumberFormat::format($amount);

            $data[] = array(
                'ID' => $transaction->getId(),
                'UNIT' => 'B' . $user->getBlock() . ' L' . $user->getLot(),
                'AMOUNT' => $amount,
                'COVERAGE' => $coverage,
                'REJECTOR' => $transaction->getProcessBy()->getName(),
            );

        }

        $docxMaker = new DocxMaker('rejected_payment.docx');

        NumberFormat::format($totalAmount);

        $docxMaker->addBody($data, 'ID');
        $docxMaker->addHeader([
            'TOTAL' => $totalAmount,
            'TITLE' => 'REJECTED PAYMENT REPORT',
            'REPORT_COVERAGE' => $this->coverage,
        ]);

        $output = $docxMaker->output();

        $pdfResponse = new PdfResponse($output, 'test.pdf');

        $this->addActionLog('Rejected Payment Report was created', LogsTag::paymentReport());

        return $pdfResponse->getResponse();

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

            $fromCoverage = new DateTime($transaction->getFromMonth());
            $toCoverage = new DateTime($transaction->getToMonth());

            $coverage = $fromCoverage->format('M Y') . ' - ' . $toCoverage->format('M Y');

            $amount = $transaction->getAmount();
            $totalAmount += $amount;

            NumberFormat::format($amount);

            $data[] = array(
                'ID' => $transaction->getId(),
                'UNIT' => 'B' . $user->getBlock() . ' L' . $user->getLot(),
                'AMOUNT' => $amount,
                'REFERENCE' => $receiptsHolder,
                'COVERAGE' => $coverage,
                'CREATED' => $transaction->getCreatedAt()->format('Y-m-d'),
            );

        }

        $docxMaker = new DocxMaker('pending_payment.docx');

        NumberFormat::format($totalAmount);

        $docxMaker->addBody($data, 'ID');
        $docxMaker->addHeader([
            'TITLE' => 'PENDING PAYMENTS REPORT',
            'TOTAL' => $totalAmount,
            'REPORT_COVERAGE' => $this->coverage,
        ]);

        $output = $docxMaker->output();

        $pdfResponse = new PdfResponse($output, 'test.pdf');

        $this->addActionLog('Pending Payment Report was created', LogsTag::paymentReport());

        return $pdfResponse->getResponse();
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


        $docxMaker = new DocxMaker('unpaid_payment.docx');

        NumberFormat::format($totalUnpaidDues);

        $docxMaker->addBody($data, 'UNIT');

        $docxMaker->addHeader(
            [
                'TOTAL' => $totalUnpaidDues,
                'REPORT_COVERAGE' => $this->coverage,
            ]);

        $output = $docxMaker->output();

        $pdfResponse = new PdfResponse($output, 'test.pdf');

        $this->addActionLog('Unpaid Payment Report was created', LogsTag::paymentReport());

        return $pdfResponse->getResponse();

    }


}