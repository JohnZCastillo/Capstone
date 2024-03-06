<?php

namespace App\controller\admin\payments;

use App\controller\admin\AdminAction;
use App\exception\AlreadyPaidException;
use App\exception\date\InvalidDateRange;
use App\exception\image\ImageNotGcashReceiptException;
use App\exception\image\UnsupportedImageException;
use App\exception\NotUniqueReferenceException;
use App\lib\GCashReceiptValidator;
use App\lib\Image;
use App\lib\ReferenceExtractor;
use App\lib\Time;
use App\model\TransactionModel;
use App\model\UserModel;
use TCPDF;

abstract class Payment extends AdminAction
{

    protected string $receiptUploadPath = "./uploads/";


    /**
     * @throws InvalidDateRange
     * @throws AlreadyPaidException
     * @throws Exception
     */
    protected function createTransaction(UserModel $user, float $amount, string $fromMonth, string $toMonth): TransactionModel
    {

        $startMonth = Time::setToFirstDayOfMonth($fromMonth);
        $endMonth = Time::setToLastDayOfMonth($toMonth);

        if (!Time::isValidDateRange($fromMonth, $toMonth)) {
            throw new InvalidDateRange();
        }

        if ($this->isPaymentLacking($user,$amount, $fromMonth, $toMonth)) {
            throw new Exception("Insufficient Payment Amount");
        }

        $months = Time::getMonths(Time::convertStringDateMonthToStringDateTime($fromMonth), Time::convertStringDateMonthToStringDateTime($toMonth));

        foreach ($months as $month) {

            if ($this->transactionService->isPaid($user, $month)) {
                throw new AlreadyPaidException($month);
            }
        }

        $transaction = new TransactionModel();
        $transaction->setAmount($amount);
        $transaction->setFromMonth(Time::convertDateStringToDateTime($startMonth));
        $transaction->setToMonth(Time::convertDateStringToDateTime($endMonth));
        $transaction->setCreatedAt(Time::timestamp());
        $transaction->setUser($user);


        return $transaction;
    }

    /**
     * @throws UnsupportedImageException
     * @throws ImageNotGcashReceiptException
     * @throws NotUniqueReferenceException
     */
    protected function saveReceipts(array $images, TransactionModel $transaction): void
    {

        $images = $_FILES['receipts'];

        if (!Image::isImage($images)) {
            throw new UnsupportedImageException();
        }

        if (!GCashReceiptValidator::isValid($images)) {
            throw new ImageNotGcashReceiptException();
        };

        $references = ReferenceExtractor::extractReference($images);

        foreach ($references as $reference) {

            if ($reference == null) {
                continue;
            }

            if ($this->receiptService->isReferenceUsed($reference,'approved')) {
                throw new NotUniqueReferenceException($reference);
            }
        }

        $storedImages = Image::storeAll($this->receiptUploadPath, $images);

        $this->transactionService->save($transaction);

        $this->receiptService->saveAll($storedImages, $transaction, $references);
    }

    public function generateTransactionReceipt(TransactionModel $transaction)
    {

        // Create a new TCPDF instance
        $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

        // Do not print the header line
        $pdf->SetPrintHeader(false);

        // Add a page
        $pdf->AddPage();

        $pdf->SetFont('times', 'B', 16);

        $pdf->Image('./resources/logo.jpeg', 10, 10, 20);
        $pdf->Cell(0, 10, 'Carissa Homes Subdivision Phase 7', 0, 1, 'C', false); // Add 'false' for no border
        $pdf->Cell(0, 10, 'Monthly Dues Invoice', 0, 1, 'C', false); // Add 'false

        $pdf->SetFont('times', '', 12);

        $transactionNumber = $transaction->getId();
        $homeownerName = $transaction->getUser()->getName();
        $property = "B". $transaction->getUser()->getBlock(). " L". $transaction->getUser()->getLot() ;

        $amount = $transaction->getAmount();
        $paymentDate = Time::convertDateTimeToDateString($transaction->getCreatedAt());
        $coverage = $transaction->getFromMonth()->format('Y-m') . ' - ' . $transaction->getToMonth()->format('Y-m') ;

        $pdf->Cell(0, 10, 'Transaction Number: ' . $transactionNumber, 0, 1);
        $pdf->Cell(0, 10, 'Property: ' . $property, 0, 1);
        $pdf->Cell(0, 10, 'Amount: ' . $amount, 0, 1);
        $pdf->Cell(0, 10, 'Payment Date: ' . $paymentDate, 0, 1);
        $pdf->Cell(0, 10, 'Coverage: ' . $coverage, 0, 1);

        $pdf->Ln(10); // Add some vertical spacing
        $pdf->MultiCell(0, 10, 'This invoice serves as proof that the payment has been made.', 0, 'L');

        $pdfContent = $pdf->Output('', 'S');

        header('Content-Type: application/pdf');
        header('Content-Disposition: attachment; filename="monthly_dues_receipt.pdf"');
        header('Content-Length: ' . strlen($pdfContent));

        echo $pdfContent;
    }

    protected function approvedTransaction(TransactionModel $transaction): void
    {

        $message = "Payment was approved";

        $admin = $this->getLoginUser();

        $transaction->setStatus('APPROVED');

        $this->transactionService->save($transaction);

        $this->transactionLogsService->log($transaction, $admin, $message, 'APPROVED');

        $message = 'Transaction with id of '.$transaction->getId()." was approved";

        $this->addActionLog($message,'payment');

    }

    protected function isPaymentLacking(UserModel $user,float $paidAmount, string $fromMonth, string $toMonth): bool
    {

        $from = Time::convertStringDateMonthToStringDateTime($fromMonth);
        $to = Time::convertStringDateMonthToStringDateTime($toMonth);

        $computedAmount = $this->transactionService->getUnpaid(
            $this->getLoginUser(),
            $this->duesService,
            $settings = $this->paymentService->findById(1),
            $from,
            $to
        );

        return $paidAmount < $computedAmount['total'];
    }

}