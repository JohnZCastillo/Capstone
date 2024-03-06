<?php

declare(strict_types=1);

namespace App\controller\admin\payments;

use App\controller\admin\AdminAction;
use App\exception\payment\PaymentReversibilityExpired;
use App\exception\payment\TransactionNotFound;
use App\model\enum\LogsTag;
use Carbon\Carbon;
use Exception;
use Psr\Http\Message\ResponseInterface as Response;

class PendingPayment extends AdminAction
{
    /**
     * {@inheritdoc}
     */
    protected function action(): Response
    {

        $id = $this->args['id'];

        try {


            $transaction = $this->transactionService->findById($id);

            $message = 'Transaction was set to PENDING by ' .  $this->getLoginUser()->getName();

            $now = Carbon::now();
            $updatedAt = Carbon::createFromFormat('Y-m-d', $transaction->getUpdatedAt()->format('Y-m-d'));

            if((int) $updatedAt->diffInDays($now) > 7){
                throw new PaymentReversibilityExpired("Cannot set payment to PENDING as it has been more than 7 days since it been processed.");
            }


            $status = $transaction->getStatus();

            $user = $this->getLoginUser();

            $transaction->setStatus('PENDING');
            $transaction->setProcessBy($user);

            $this->transactionService->save($transaction);

            if($status == 'APPROVED'){
                $this->incomeService->delete($transaction);
            }

            $this->transactionLogsService->log($transaction, $user, $message, 'PENDING');
            $action = "Payment with id of " . $transaction->getId() . " was set to PENDING";

            $this->addActionLog($action, LogsTag::payment());

        } catch (TransactionNotFound $transactionNotFound) {
            $this->addErrorMessage('Transaction Not Found!');
            return $this->redirect('/admin/payments');
        } catch (PaymentReversibilityExpired $exception) {
            $this->addErrorMessage($exception->getMessage());
        } catch (Exception $exception) {
            $this->addErrorMessage($exception->getMessage());

//            $this->addErrorMessage('An  Internal Error Has Occurred, pleas check logs');
        }

        return $this->redirect("/admin/transaction/$id");
    }
}