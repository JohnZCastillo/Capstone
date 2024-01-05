<?php

declare(strict_types=1);

namespace App\controller\user\payments;

use App\controller\user\UserAction;
use App\exception\NotAuthorizeException;
use App\exception\payment\TransactionNotFound;
use App\lib\Time;
use Psr\Http\Message\ResponseInterface as Response;

class Transaction extends UserAction
{
    /**
     * {@inheritdoc}
     */
    protected function action(): Response
    {

        $id = $this->args['id'];

        try {

            $transaction = $this->transactionService->findById($id);

            $sameBlock = $transaction->getUser()->getBlock() === $this->getLoginUser()->getBlock();
            $sameLot = $transaction->getUser()->getLot() === $this->getLoginUser()->getLot();

            $user = $this->getLoginUser();

            $amount = $this->duesService->getDueInRange(
                Time::toMonth($transaction->getFromMonth()),
                Time::toMonth($transaction->getToMonth())
            );

            if (!($sameBlock && $sameLot)) {
                throw new NotAuthorizeException('You`re Not Authorized to view this content');
            }

        } catch (NotAuthorizeException $notAuthorizeException) {
            $this->addErrorMessage($notAuthorizeException->getMessage());
            return $this->redirect('/home');
        } catch (TransactionNotFound $e) {
            $this->addErrorMessage($e->getMessage());
            return $this->redirect('/home');
        }

        return $this->view('user/pages/transaction.html', [
            'transaction' => $transaction,
            'amountDue' => $amount,
            'receipts' => $transaction->getReceipts(),
        ]);
    }
}