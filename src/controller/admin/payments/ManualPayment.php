<?php

declare(strict_types=1);

namespace App\controller\admin\payments;

use App\controller\admin\AdminAction;
use App\exception\AlreadyPaidException;
use App\exception\ContentLock;
use App\exception\date\InvalidDateRange;
use App\exception\image\ImageNotGcashReceiptException;
use App\exception\image\UnsupportedImageException;
use App\exception\NotUniqueReferenceException;
use App\exception\payment\InvalidPaymentAmount;
use App\exception\payment\InvalidReference;
use App\exception\payment\TransactionNotFound;
use App\lib\GCashReceiptValidator;
use App\lib\Image;
use App\lib\ReferenceExtractor;
use App\lib\Time;
use App\model\enum\LogsTag;
use App\model\TransactionModel;
use App\model\UserModel;
use Exception;
use Psr\Http\Message\ResponseInterface as Response;
use Respect\Validation\Validator as v;

class ManualPayment extends Payment
{

    /**
     * {@inheritdoc}
     */
    protected function action(): Response
    {

        $formData = $this->getFormData();

        $fromMonth = $formData['from'];
        $toMonth = $formData['to'];
        $amount = $formData['amount'];
        $block = $formData['block'];
        $lot = $formData['lot'];

        try {
            $user = $this->userService->findManualPayment($block,$lot);
            $transaction = $this->createTransaction($user, (float) $amount, $fromMonth, $toMonth);
            $this->transactionService->save($transaction);

            $amount = $transaction->getAmount();

            $amountToPay = $this->duesService->getDueInRange(
                Time::toMonth($transaction->getFromMonth()),
                Time::toMonth($transaction->getToMonth())
            );

            if ($amount !== $amountToPay) {
                throw new InvalidPaymentAmount("Payment must be equal to $amountToPay");
            }


            $this->approvedTransaction($transaction);
            $this->generateTransactionReceipt($transaction);

            $actionMessage = 'Manual payment wit id of '. $transaction->getId(). ' was created';

            $this->addActionLog($actionMessage, LogsTag::manualPayment());

        } catch (AlreadyPaidException $paidException) {
            $this->addErrorMessage($paidException->getMessage());
        } catch (InvalidDateRange $invalidDateRange) {
            $this->addErrorMessage("You have inputted an invalid date range");
        }catch (InvalidPaymentAmount $invalidPaymentAmount) {
            $this->addErrorMessage($invalidPaymentAmount->getMessage());
        }  catch (Exception $e) {
            $this->addErrorMessage('Internal Error, please check logs');
        }
        return $this->redirect('/admin/payments');
    }

}