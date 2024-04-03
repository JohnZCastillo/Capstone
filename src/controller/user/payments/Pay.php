<?php

declare(strict_types=1);

namespace App\controller\user\payments;

use App\controller\user\UserAction;
use App\exception\AlreadyPaidException;
use App\exception\date\InvalidDateRange;
use App\exception\image\ImageNotGcashReceiptException;
use App\exception\image\UnsupportedImageException;
use App\exception\InvalidInput;
use App\exception\NotUniqueReferenceException;
use App\exception\payment\InvalidPaymentAmount;
use App\lib\GCashReceiptValidator;
use App\lib\Image;
use App\lib\ReferenceExtractor;
use App\lib\Time;
use App\model\ReceiptModel;
use App\model\TransactionModel;
use App\model\UserModel;
use Carbon\Carbon;
use Exception;
use Slim\Psr7\Response;
use Respect\Validation\Validator as v;

class Pay extends UserAction
{

    private const RECEIPT_DIR = "./uploads/";

    public function action(): Response
    {
        $content = $this->getFormData();
        $user = $this->getLoginUser();

        try {

            if (!v::number()->positive()->notEmpty()->validate($content['amount'])) {
                throw new InvalidInput('Invalid Amount');
            }

            if (!v::date('Y-m')->notEmpty()->validate($content['startDate'])) {
                throw new InvalidInput('Invalid Start Date');
            }

            if (!v::date('Y-m')->notEmpty()->validate($content['endDate'])) {
                throw new InvalidInput('Invalid End Date');
            }


            $fromMonth = $content['startDate'];
            $toMonth = $content['endDate'];
            $amount = $content['amount'];
            $images = $_FILES['receipts'];

            $transaction = $this->createTransaction($user, (float)$amount, $fromMonth, $toMonth);

            $amount = $this->duesService->getDueInRange(
                $transaction->getFromMonth()->format('Y-m'),
                $transaction->getToMonth()->format('Y-m'),
            );

            if ($amount !== ((float)$content['amount'])) {
                throw new InvalidPaymentAmount('Invalid Payment Amount');
            }

            $this->saveReceipts($images, $transaction);

            $this->addSuccessMessage('Payment created, please wait an admin review this');

            return $this->redirect('/home',302);

        } catch (InvalidInput $invalidInput) {
            $this->addErrorMessage($invalidInput->getMessage());
        } catch (InvalidPaymentAmount $invalidPaymentAmount) {
            $this->addErrorMessage($invalidPaymentAmount->getMessage());
        } catch (UnsupportedImageException $imageException) {
            $this->addErrorMessage('Your Attach Receipt was Invalid. Please make sure that it as an image');
        } catch (InvalidDateRange $invalidDateRange) {
            $this->addErrorMessage('You have inputted an invalid date range');
        } catch (ImageNotGcashReceiptException $notGcashReceipt) {
            $this->addErrorMessage('The image that was sent was not a GCash receipt');
        } catch (NotUniqueReferenceException $referenceException) {
            $this->addErrorMessage($referenceException->getMessage());
        } catch (AlreadyPaidException $paidException) {
            $this->addErrorMessage($paidException->getMessage());
        } catch (Exception $exception) {
            $this->addErrorMessage($exception->getMessage());
            //            $this->addErrorMessage('An Internal Error Occurred');
        }

        return $this->redirect('/home');
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
    private function saveReceipts(array $images, TransactionModel $transaction): void
    {

        $referencePattern = $this->systemSettingService->findById()->getRegex();

        if (!Image::isImage($images)) {
            throw new UnsupportedImageException();
        }

        if (!GCashReceiptValidator::isValid($images)) {
            throw new ImageNotGcashReceiptException();
        };

        $ocr = ReferenceExtractor::extractOcr($images);
        $references = ReferenceExtractor::reference($ocr, $referencePattern);
        $amount = ReferenceExtractor::extractAmount($ocr);
        $receipts = [];

        foreach ($references as $index => $reference) {

            if ($reference == null) {
                continue;
            }

            if ($this->receiptService->isReferenceUsed($reference, 'approved')) {
                throw new NotUniqueReferenceException($reference);
            }

            $receipt = new ReceiptModel();
            $receipt->setCor($ocr[$index]);
            $receipt->setAmountSent((float)$amount[$index]);
            $receipt->setTransaction($transaction);
            $receipt->setReferenceNumber($reference);

            $receipts[] = $receipt;
        }

        $this->transactionService->save($transaction);

        $storedImages = Image::storeAll(self::RECEIPT_DIR, $images);

        $this->receiptService->saveAllReceipt($storedImages, $receipts);
    }

}