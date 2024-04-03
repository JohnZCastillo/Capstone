<?php

declare(strict_types=1);

namespace App\controller\admin\payments;

use App\exception\AlreadyPaidException;
use App\exception\date\InvalidDateRange;
use App\exception\payment\InvalidPaymentAmount;
use App\lib\Time;
use App\model\enum\LogsTag;
use App\model\ReceiptModel;
use Exception;
use Psr\Http\Message\ResponseInterface as Response;

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

            if(!$this->areaService->exist($block,$lot)){
                throw new InvalidPaymentAmount("Property doest not exist!");
            }

            $user = $this->userService->findManualPayment($block,$lot);
            $transaction = $this->createTransaction($user, (float) $amount, $fromMonth, $toMonth);

            $transaction->setProcessBy($this->getLoginUser());
            $transaction->setPaymentMethod('cash');

            $receipt = new ReceiptModel();

            $this->transactionService->save($transaction);

            $receipt->setReferenceNumber('Manual Payment ' . $transaction->getId());
            $receipt->setPath(null);
            $receipt->setTransaction($transaction);
            $receipt->setAmountSent($transaction->getAmount());

            $this->receiptService->save($receipt);

            $amount = $transaction->getAmount();

            $amountToPay = $this->duesService->getDueInRange(
                $transaction->getFromMonth()->format('Y-m'),
                $transaction->getToMonth()->format('Y-m'),
            );

            if ($amount !== $amountToPay) {
                throw new InvalidPaymentAmount("Payment must be equal to $amountToPay");
            }

            $this->approvedTransaction($transaction);
//            $this->generateTransactionReceipt($transaction);

            $actionMessage = 'Manual payment wit id of '. $transaction->getId(). ' was created';

            $this->addActionLog($actionMessage, LogsTag::manualPayment());

            $this->setupIncome($transaction);

            return $this->generateReceipt($transaction);

        } catch (AlreadyPaidException $paidException) {
            $this->addErrorMessage($paidException->getMessage());
        } catch (InvalidDateRange $invalidDateRange) {
            $this->addErrorMessage("You have inputted an invalid date range");
        }catch (InvalidPaymentAmount $invalidPaymentAmount) {
            $this->addErrorMessage($invalidPaymentAmount->getMessage());
        }  catch (Exception $e) {
            $this->addErrorMessage('Something went wrong please try again');
        }

        return $this->redirect('/admin/payments');
    }

}