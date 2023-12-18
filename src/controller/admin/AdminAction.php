<?php

declare(strict_types=1);

namespace App\controller\admin;

use App\controller\Action;
use App\lib\Time;
use App\model\PaymentModel;
use App\service\DuesService;
use App\service\PaymentService;
use App\service\TransactionService;
use App\service\UserService;
use Psr\Log\LoggerInterface;
use Slim\Flash\Messages;

abstract class AdminAction extends Action
{
    protected UserService $userService;
    protected  PaymentService $paymentService;

    protected  TransactionService $transactionService;

    protected DuesService $duesService;

    protected  Messages $flashMessage;

    /**
     * @param UserService $userService
     * @param PaymentService $paymentService
     * @param TransactionService $transactionService
     * @param DuesService $duesService
     * @param Messages $flashMessage
     */
    public function __construct(UserService $userService, PaymentService $paymentService, TransactionService $transactionService, DuesService $duesService, Messages $flashMessage)
    {
        $this->userService = $userService;
        $this->paymentService = $paymentService;
        $this->transactionService = $transactionService;
        $this->duesService = $duesService;
        $this->flashMessage = $flashMessage;
    }

    protected function getPaymentSettings(): PaymentModel|null
    {
        $id = 1;
        return $this->paymentService->findById($id);
    }

    protected function getDues($startOfPaymentYear): array
    {
        try {

            $dues = [];

            $datesForMonths = Time::getDatesForMonthsOfYear($startOfPaymentYear);

            foreach ($datesForMonths as $month => $dates) {
                $dues[] = [
                    "date" => $dates,
                    "amount" => $this->duesService->getDue($dates),
                    "savePoint" => $this->duesService->isSavePoint($dates),
                    "month" => $dates->format('M'),
                ];
            }

            return $dues;

        } catch (Exception $e) {
            return [];
        }
    }

    protected function  addErrorMessage($message)
    {
        $this->flashMessage->addMessage('errorMessage', $message);
    }

    protected function  addSuccessMessage($message)
    {
        $this->flashMessage->addMessage('successMessage', $message);
    }

    protected function  addMessage($key,$message)
    {
        $this->flashMessage->addMessage($key, $message);
    }

}