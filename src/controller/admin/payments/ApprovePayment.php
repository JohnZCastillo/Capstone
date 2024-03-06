<?php

declare(strict_types=1);

namespace App\controller\admin\payments;

use App\controller\admin\AdminAction;
use App\exception\AlreadyPaidException;
use App\exception\ContentLock;
use App\exception\fund\FundNotFound;
use App\exception\InvalidInput;
use App\exception\NotUniqueReferenceException;
use App\exception\payment\InvalidPaymentAmount;
use App\exception\payment\InvalidReference;
use App\exception\payment\TransactionNotFound;
use App\model\enum\LogsTag;
use Exception;
use Psr\Http\Message\ResponseInterface as Response;
use Respect\Validation\Validator as v;

class ApprovePayment extends AdminAction
{
    protected function action(): Response
    {

        $formData = $this->getFormData();

        $id = $formData['id'];

        try {

            $transaction = $this->transactionService->findById($id);

            if ($transaction->getStatus() != "PENDING") {
                throw new ContentLock('Cannot Edit Content');
            }

            if($this->transactionService->isPaidForMonth(
                $transaction->getUser(),
                $transaction->getFromMonth(),
                $transaction->getToMonth(),
            )){
                throw new AlreadyPaidException('user already paid for this month');
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

            $transaction->setUpdatedAt(new \DateTime());
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
            $this->addErrorMessage('An  Internal Error Has Occurred, pleas check logs');
        }

        return $this->redirect("/admin/transaction/$id");
    }


}