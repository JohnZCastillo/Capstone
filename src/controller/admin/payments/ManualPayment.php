<?php

declare(strict_types=1);

namespace App\controller\admin\payments;

use App\exception\AlreadyPaidException;
use App\exception\date\InvalidDateRange;
use App\exception\payment\InvalidPaymentAmount;
use App\lib\Time;
use App\model\enum\LogsTag;
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

            $this->setupIncome($transaction);

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