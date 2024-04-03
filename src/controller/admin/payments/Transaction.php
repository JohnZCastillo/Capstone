<?php

declare(strict_types=1);

namespace App\controller\admin\payments;

use App\controller\admin\AdminAction;
use App\exception\payment\TransactionNotFound;
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
                $transaction->getFromMonth()->format('Y-m'),
                $transaction->getToMonth()->format('Y-m'),
            );

            $references = $this->transactionService->getReferences($transaction);


            $isNotUniqueReferences = $this->receiptService->isNotUniqueReferences($references);

            $transactions = $this->transactionService->getByApprovedReferences($transaction,$references);

            return $this->view('admin/pages/transaction.html', [
                'transaction' => $transaction,
                'receipts' => $transaction->getReceipts(),
                'user' => $transaction->getUser(),
                'loginUser' => $this->getLoginUser(),
                'totalDue' => $totalDue,
                'transactions' => $transactions,
                'isNotUniqueReferences' => $isNotUniqueReferences,
            ]);

        } catch (TransactionNotFound $transactionNotFound) {
            $this->addErrorMessage('Transaction Not Found!');
        } catch (Exception $exception) {
//            $this->addErrorMessage('An  Internal Error Has Occurred, pleas check logs');
            $this->addErrorMessage($exception->getMessage());
        }

        return $this->redirect('/admin/payments');
    }
}
