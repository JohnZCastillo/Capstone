<?php

namespace App\controller;

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
use Exception;
use TCPDF;

class PaymentController extends Controller
{

    private string $receiptUploadPath = "./uploads/";

    public function userPay($request, $response, $args)
    {

        $content = $request->getParsedBody();

        $user = $this->getLogin();
        $fromMonth = $content['startDate'];
        $toMonth = $content['endDate'];
        $amount = $content['amount'];
        $images = $_FILES['receipts'];

        try {

            $transaction = $this->createTransaction($user, $amount, $fromMonth, $toMonth);
            $this->saveReceipts($images, $transaction);
            $this->saveUserLog("User Had made a payment with id of " . $transaction->getId(), $user);

        } catch (UnsupportedImageException $imageException) {
            $imageExceptionMessage = "Your Attach Receipt was Invalid. Please make sure that it as an image";
            $this->flashMessages->addMessage("errorMessage", $imageExceptionMessage);
        } catch (InvalidDateRange $invalidDateRange) {
            $invalidDateRangeMessage = "You have inputted an invalid date range";
            $this->flashMessages->addMessage("ErrorMessage", $invalidDateRangeMessage);
        } catch (ImageNotGcashReceiptException $notGcashReceipt) {
            $notGcashMessage = "The image that was sent was not a GCash receipt";
            $this->flashMessages->addMessage("ErrorMessage", $notGcashMessage);
        } catch (NotUniqueReferenceException $referenceException) {
            $this->flashMessages->addMessage("ErrorMessage", $referenceException->getMessage());
        } catch (AlreadyPaidException $paidException) {
            $this->flashMessages->addMessage("ErrorMessage", $paidException->getMessage());
        } finally {
            return $response
                ->withHeader('Location', "/home")
                ->withStatus(302);
        }
    }

    public function manualPayment($request, $response, $args)
    {

        $content = $request->getParsedBody();

        $fromMonth = $content['from'];
        $toMonth = $content['to'];
        $amount = $content['amount'];
        $block = $content['block'];
        $lot = $content['lot'];

        try {
            $user = $this->userSerivce->findManualPayment($block,$lot);
            $transaction = $this->createTransaction($user, $amount, $fromMonth, $toMonth);
            $this->transactionService->save($transaction);
            $this->approvedTransaction($transaction);
            $this->generateTransactionReceipt($transaction);
        } catch (AlreadyPaidException $paidException) {
            $this->flashMessages->addMessage("errorMessage", $paidException->getMessage());
        } catch (InvalidDateRange $invalidDateRange) {
            $invalidDateRangeMessage = "You have inputted an invalid date range";
            $this->flashMessages->addMessage("errorMessage", $invalidDateRangeMessage);
        } catch (Exception $e) {
            $this->flashMessages->addMessage("errorMessage", $e->getMessage());
        }

        return $response
            ->withHeader('Location', "/admin/home")
            ->withStatus(302);

    }

    /**
     * @throws InvalidDateRange
     * @throws AlreadyPaidException
     * @throws Exception
     */
    private function createTransaction(UserModel $user, float $amount, string $fromMonth, string $toMonth): TransactionModel
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


    public function getReceipt($request, $response, $args)
    {

        $transactionId = $args['id'];
        $transaction = $this->transactionService->findById($transactionId);

        $this->generateTransactionReceipt($transaction);

    }

        /**
     * @throws UnsupportedImageException
     * @throws ImageNotGcashReceiptException
     * @throws NotUniqueReferenceException
     */
    private function saveReceipts(array $images, TransactionModel $transaction): void
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

            if (!$this->receiptService->isUniqueReference($reference)) {
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
        $coverage = $transaction->getFromMonth() . ' - ' . $transaction->getToMonth();

        $pdf->Cell(0, 10, 'Transaction Number: ' . $transactionNumber, 0, 1);
        $pdf->Cell(0, 10, 'Homeowner: ' . $homeownerName, 0, 1);
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

    private function approvedTransaction(TransactionModel $transaction): void
    {

        $message = "Payment was approved";

        $admin = $this->getLogin();

        $transaction->setStatus('APPROVED');

        $this->transactionService->save($transaction);

        $this->logsService->log($transaction, $admin, $message, 'APPROVED');

    }

    private function rejectTransaction(TransactionModel $transaction): void
    {

        $message = "Payment was rejected";

        $admin = $this->getLogin();

        $transaction->setStatus('REJECTED');

        $this->transactionService->save($transaction);

        $this->logsService->log($transaction, $admin, $message, 'REJECTED');

    }

    private function isPaymentLacking(UserModel $user,float $paidAmount, string $fromMonth, string $toMonth): bool
    {

        $from = Time::convertStringDateMonthToStringDateTime($fromMonth);
        $to = Time::convertStringDateMonthToStringDateTime($toMonth);

        $computedAmount = $this->transactionService->getUnpaid(
            $this->getLogin(),
            $this->duesService,
            $this->getPaymentSettings(),
            $from,
            $to
        );

        return $paidAmount < $computedAmount['total'];
    }

}