<?php

declare(strict_types=1);

namespace App\controller\admin\payments;

use App\controller\admin\AdminAction;
use App\exception\payment\TransactionNotFound;
use App\lib\Time;
use Exception;
use Psr\Http\Message\ResponseInterface as Response;

class Transaction extends AdminAction
{
    /**
     * {@inheritdoc}
     */
    protected function action(): Response
    {

        $id = $this->args['id'];

        try {

            $transaction = $this->transactionService->findById($id);

            $totalDue = $this->duesService->getDueInRange(
                Time::toMonth($transaction->getFromMonth()),
                Time::toMonth($transaction->getToMonth())
            );

            return $this->view('admin/pages/transaction.html', [
                'transaction' => $transaction,
                'receipts' => $transaction->getReceipts(),
                'user' => $transaction->getUser(),
                'loginUser' => $this->getLoginUser(),
                'totalDue' => $totalDue,
            ]);

        } catch (TransactionNotFound $transactionNotFound) {
            $this->addErrorMessage('Transaction Not Found!');
        } catch (Exception $exception) {
            $this->addErrorMessage('An  Internal Error Has Occurred, pleas check logs');
        }

        return $this->redirect('/admin/payments');
    }
}