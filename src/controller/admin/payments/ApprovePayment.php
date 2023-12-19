<?php

declare(strict_types=1);

namespace App\controller\admin\payments;

use App\controller\admin\AdminAction;
use App\exception\ContentLock;
use App\exception\NotUniqueReferenceException;
use App\exception\payment\InvalidReference;
use App\exception\payment\TransactionNotFound;
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

            $user = $this->getLoginUser();

            $references = $formData['field'];
            $receipts = $transaction->getReceipts();

            for ($i = 0; $i < count($receipts); $i++) {

                $reference = $references[$i];
                $receipt = $receipts[$i];

                if (empty($reference) || !(v::alnum()->validate($reference))) {
                    throw new InvalidReference();
                }

                if ($this->receiptService->isReferenceUsed($reference, 'approved')) {
                    throw new NotUniqueReferenceException($reference);
                }

                var_dump($this->receiptService->isReferenceUsed($reference, 'approved'));

                $this->receiptService->confirm($receipt, $reference);
            }

            $transaction->setStatus('APPROVED');
            $transaction->setApprovedBy($user);

            $this->transactionService->save($transaction);

            $this->transactionLogsService->log($transaction, $user, 'Payment was approved', 'APPROVED');
            $action = "Payment with id of " . $transaction->getId() . " was approved";

            $this->addActionLog($action, 'payment');

        } catch (TransactionNotFound $transactionNotFound) {
            $this->addErrorMessage('Transaction Not Found!');
            return $this->redirect('/admin/payments');
        } catch (NotUniqueReferenceException $notUniqueReferenceException) {
            $this->addErrorMessage($notUniqueReferenceException->getMessage());
        } catch (ContentLock $contentLock) {
            $this->addErrorMessage($contentLock->getMessage());
        } catch (InvalidReference $invalidReference) {
            $this->addErrorMessage($invalidReference->getMessage());
        } catch (Exception $exception) {
            $this->addErrorMessage('An  Internal Error Has Occurred, pleas check logs');
        }

        return $this->redirect("/admin/transaction/$id");
    }
}