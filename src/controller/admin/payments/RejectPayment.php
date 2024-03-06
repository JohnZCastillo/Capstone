<?php

declare(strict_types=1);

namespace App\controller\admin\payments;

use App\controller\admin\AdminAction;
use App\exception\ContentLock;
use App\exception\InvalidInput;
use App\exception\payment\TransactionNotFound;
use App\model\enum\LogsTag;
use Exception;
use Psr\Http\Message\ResponseInterface as Response;
use Respect\Validation\Validator as v;

class RejectPayment extends AdminAction
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

            $message = $formData['message'];

            if (!v::stringType()->notEmpty()->validate($message)) {
                throw new InvalidInput('Message cannot be empty');
            }

            if ($transaction->getStatus() != "PENDING") {
                throw new ContentLock('Cannot Edit Content');
            }

            if (!v::alnum(' ')->validate($message)) {
                throw new InvalidInput('Message must only be string characters');
            }

            $user = $this->getLoginUser();

            $transaction->setStatus('REJECTED');
            $transaction->setProcessBy($user);
            $transaction->setUpdatedAt(new \DateTime());

            $this->transactionService->save($transaction);

            $this->transactionLogsService->log($transaction, $user, $message, 'APPROVED');
            $action = "Payment with id of " . $transaction->getId() . " was rejected";

            $this->addActionLog($action, LogsTag::payment());

        } catch (TransactionNotFound $transactionNotFound) {
            $this->addErrorMessage('Transaction Not Found!');
            return $this->redirect('/admin/payments');
        } catch (ContentLock $contentLock) {
            $this->addErrorMessage($contentLock->getMessage());
        } catch (InvalidInput $invalidInput) {
            $this->addErrorMessage($invalidInput->getMessage());
        } catch (Exception $exception) {
            $this->addErrorMessage('An  Internal Error Has Occurred, pleas check logs');
        }

        return $this->redirect("/admin/transaction/$id");
    }
}