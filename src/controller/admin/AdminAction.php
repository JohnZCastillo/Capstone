<?php

declare(strict_types=1);

namespace App\controller\admin;

use App\controller\Action;
use App\lib\Time;
use App\model\LogsModel;
use App\model\PaymentModel;
use App\model\UserModel;
use App\service\DuesService;
use App\service\LogsService;
use App\service\PaymentService;
use App\service\ReceiptService;
use App\service\TransactionLogsService;
use App\service\TransactionService;
use App\service\UserService;
use DateTime;
use Psr\Log\LoggerInterface;
use Slim\Flash\Messages;

abstract class AdminAction extends Action
{
    protected UserService $userService;
    protected PaymentService $paymentService;
    protected TransactionService $transactionService;
    protected DuesService $duesService;
    protected  ReceiptService $receiptService;
    protected Messages $flashMessage;

    protected  LogsService $logsService;

    protected  TransactionLogsService $transactionLogsService;

    /**
     * @param UserService $userService
     * @param PaymentService $paymentService
     * @param TransactionService $transactionService
     * @param DuesService $duesService
     * @param ReceiptService $receiptService
     * @param Messages $flashMessage
     * @param LogsService $logsService
     * @param TransactionLogsService $transactionLogsService
     */
    public function __construct(UserService $userService, PaymentService $paymentService, TransactionService $transactionService, DuesService $duesService, ReceiptService $receiptService, Messages $flashMessage, LogsService $logsService, TransactionLogsService $transactionLogsService)
    {
        $this->userService = $userService;
        $this->paymentService = $paymentService;
        $this->transactionService = $transactionService;
        $this->duesService = $duesService;
        $this->receiptService = $receiptService;
        $this->flashMessage = $flashMessage;
        $this->logsService = $logsService;
        $this->transactionLogsService = $transactionLogsService;
    }

    protected function addErrorMessage($message)
    {
        $this->flashMessage->addMessage('errorMessage', $message);
    }

    protected function addSuccessMessage($message)
    {
        $this->flashMessage->addMessage('successMessage', $message);
    }

    protected function addMessage($key, $message)
    {
        $this->flashMessage->addMessage($key, $message);
    }

    protected function getLoginUser():UserModel
    {
        return $this->userService->findById(1);
    }


    protected function addActionLog(string $message, string $tag): void
    {
        $actionLog = new LogsModel();
        $actionLog->setAction($message);
        $actionLog->setTag($tag);
        $actionLog->setUser($this->getLoginUser());
        $actionLog->setCreatedAt(new DateTime());
        $this->logsService->addLog($actionLog);
    }

}