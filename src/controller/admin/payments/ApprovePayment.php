<?php

declare(strict_types=1);

namespace App\controller\admin\payments;

use App\controller\admin\AdminAction;
use App\exception\ContentLock;
use App\exception\fund\FundNotFound;
use App\exception\InvalidInput;
use App\exception\NotUniqueReferenceException;
use App\exception\payment\InvalidPaymentAmount;
use App\exception\payment\InvalidReference;
use App\exception\payment\TransactionNotFound;
use App\lib\Time;
use App\model\budget\IncomeModel;
use App\model\enum\LogsTag;
use App\model\TransactionModel;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Exception;
use Psr\Http\Message\ResponseInterface as Response;
use Respect\Validation\Validator as v;

class ApprovePayment extends AdminAction
{
    /**
     * {@inheritdoc}
     */
    protected function action(): Response
    {

        $formData = $this->getFormData();

        $id = $formData['id'];

        try {

            $transaction = $this->transactionService->findById($id);

            if ($transaction->getStatus() != "PENDING") {
                throw new ContentLock('Cannot Edit Content');
            }

            $amount = $transaction->getAmount();

            $amountToPay = $this->duesService->getDueInRange(
                Time::toMonth($transaction->getFromMonth()),
                Time::toMonth($transaction->getToMonth())
            );

            if ($amount !== $amountToPay) {
                throw new InvalidPaymentAmount("Payment must be equal to $amountToPay");
            }

            $user = $this->getLoginUser();

            $references = $formData['field'];
            $receipts = $transaction->getReceipts();

            for ($i = 0; $i < count($receipts); $i++) {

                $reference = $references[$i];
                $receipt = $receipts[$i];

                if (!v::stringType()->notEmpty()->validate($reference)) {
                    throw new InvalidInput('Reference cannot be empty');
                }

                if (!v::alnum()->validate($reference)) {
                    throw new InvalidReference();
                }

                if ($this->receiptService->isReferenceUsed($reference, 'approved')) {
                    throw new NotUniqueReferenceException($reference);
                }

                $this->receiptService->confirm($receipt, $reference);
            }

            $transaction->setStatus('APPROVED');
            $transaction->setProcessBy($user);

            $this->transactionService->save($transaction);

            $this->transactionLogsService->log($transaction, $user, 'Payment was approved', 'APPROVED');
            $action = "Payment with id of " . $transaction->getId() . " was approved";

            $this->addActionLog($action, LogsTag::payment());

            $this->setupIncome($transaction);

        } catch (TransactionNotFound $transactionNotFound) {
            $this->addErrorMessage('Transaction Not Found!');
            return $this->redirect('/admin/payments');
        } catch (NotUniqueReferenceException $notUniqueReferenceException) {
            $this->addErrorMessage($notUniqueReferenceException->getMessage());
        } catch (InvalidPaymentAmount $invalidPaymentAmount) {
            $this->addErrorMessage($invalidPaymentAmount->getMessage());
        } catch (ContentLock $contentLock) {
            $this->addErrorMessage($contentLock->getMessage());
        } catch (InvalidReference $invalidReference) {
            $this->addErrorMessage($invalidReference->getMessage());
        } catch (InvalidInput $invalidInput) {
            $this->addErrorMessage($invalidInput->getMessage());
        } catch (FundNotFound $invalidInput) {
            $this->addErrorMessage($invalidInput->getMessage());
        } catch (Exception $exception) {
            $this->addErrorMessage($exception->getMessage());
//            $this->addErrorMessage('An  Internal Error Has Occurred, pleas check logs');
        }

        return $this->redirect("/admin/transaction/$id");
    }


}