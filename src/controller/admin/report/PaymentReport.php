<?php

namespace App\controller\admin\report;

use App\controller\admin\AdminAction;
use App\lib\DocxMaker;
use App\lib\NumberFormat;
use App\lib\PdfResponse;
use App\lib\Time;
use App\model\enum\LogsTag;
use App\model\UserModel;
use DateTime;
use Slim\Psr7\Response;

class PaymentReport extends AdminAction
{

    public function action(): Response
    {

        try {
            $formData = $this->getFormData();

            $from = $formData['from'];
            $to = $formData['to'];

            $block = $formData['block'] ?? null;
            $lot = $formData['lot'] ?? null;

            $status = $formData['reportStatus'][0];

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

        return$this->redirect('/admin/payments');
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

            $data[] = array(
                'ID' => $transaction->getId(),
                'UNIT' => 'B' . $user->getBlock() . ' L' . $user->getLot(),
                'AMOUNT' => $transaction->getAmount(),
                'REFERENCE' => $receiptsHolder,
                'COVERAGE' => $coverage,
                'CREATED' => $transaction->getCreatedAt()->format('Y-m-d'),
            );


        }

        $docxMaker = new DocxMaker('approve_payment.docx');

        $docxMaker->addBody($data, 'ID');
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

        foreach ($transactions as $transaction) {

            $user = $transaction->getUser();

            $receipts = $transaction->getReceipts();

            $fromCoverage = new DateTime($transaction->getFromMonth());
            $toCoverage = new DateTime($transaction->getToMonth());

            $coverage = $fromCoverage->format('M Y') . ' - ' . $toCoverage->format('M Y');

            $data[] = array(
                'ID' => $transaction->getId(),
                'UNIT' => 'B' . $user->getBlock() . ' L' . $user->getLot(),
                'AMOUNT' => $transaction->getAmount(),
                'COVERAGE' => $coverage,
                'REJECTOR' => $transaction->getRejectedBy()->getName(),
            );

        }

        $docxMaker = new DocxMaker('rejected_payment.docx');

        $docxMaker->addBody($data, 'ID');
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

            $data[] = array(
                'ID' => $transaction->getId(),
                'UNIT' => 'B' . $user->getBlock() . ' L' . $user->getLot(),
                'AMOUNT' => $transaction->getAmount(),
                'REFERENCE' => $receiptsHolder,
                'COVERAGE' => $coverage,
                'CREATED' => $transaction->getCreatedAt()->format('Y-m-d'),
            );

        }

        $docxMaker = new DocxMaker('approve_payment.docx');

        $docxMaker->addBody($data, 'ID');
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
                Time::convertToString(Time::startMonth($from)),
                Time::convertToString(Time::startMonth($to)),
            )['total'];

            $copy = $total;

            NumberFormat::format($copy);

            $data[] = [
                'AMOUNT' => $copy,
                'UNIT' => 'B'.$user->getBlock() . ' L'.$user->getLot()
            ];

            $totalUnpaidDues += $total;
        }


        $docxMaker = new DocxMaker('unpaid_payment.docx');

        NumberFormat::format($totalUnpaidDues);

        $docxMaker->addBody($data, 'UNIT');

        $docxMaker->addHeader(['TOTAL' => $totalUnpaidDues]);

        $output = $docxMaker->output();

        $pdfResponse = new PdfResponse($output, 'test.pdf');

        $this->addActionLog('Unpaid Payment Report was created', LogsTag::paymentReport());

        return $pdfResponse->getResponse();

    }


}