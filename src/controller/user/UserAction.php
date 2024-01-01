<?php

declare(strict_types=1);

namespace App\controller\user;

use App\controller\Action;
use App\lib\Login;
use App\lib\LoginDetails;
use App\model\LoginHistoryModel;
use App\model\LogsModel;
use App\model\UserModel;
use App\service\AnnouncementService;
use App\service\DuesService;
use App\service\IssuesService;
use App\service\LoginHistoryService;
use App\service\LogsService;
use App\service\PaymentService;
use App\service\ReceiptService;
use App\service\TransactionService;
use App\service\UserService;
use DateTime;
use Psr\Log\LoggerInterface;
use Slim\Flash\Messages;

abstract class UserAction extends Action
{
    protected UserService $userService;
    protected Messages $flashMessage;
    protected  LogsService $logsService;
    protected  LoginHistoryService $loginHistoryService;

    protected  TransactionService $transactionService;

    protected DuesService $duesService;

    protected  PaymentService $paymentService;

    protected ReceiptService $receiptService;

    protected IssuesService $issuesService;

    protected  AnnouncementService $announcementService;

    /**
     * @param UserService $userService
     * @param Messages $flashMessage
     * @param LogsService $logsService
     * @param LoginHistoryService $loginHistoryService
     * @param TransactionService $transactionService
     * @param DuesService $duesService
     * @param PaymentService $paymentService
     * @param ReceiptService $receiptService
     * @param IssuesService $issuesService
     * @param AnnouncementService $announcementService
     */
    public function __construct(UserService $userService, Messages $flashMessage, LogsService $logsService, LoginHistoryService $loginHistoryService, TransactionService $transactionService, DuesService $duesService, PaymentService $paymentService, ReceiptService $receiptService, IssuesService $issuesService, AnnouncementService $announcementService)
    {
        $this->userService = $userService;
        $this->flashMessage = $flashMessage;
        $this->logsService = $logsService;
        $this->loginHistoryService = $loginHistoryService;
        $this->transactionService = $transactionService;
        $this->duesService = $duesService;
        $this->paymentService = $paymentService;
        $this->receiptService = $receiptService;
        $this->issuesService = $issuesService;
        $this->announcementService = $announcementService;
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

    protected function getLoginUser(): UserModel
    {
        return $this->userService->findById(Login::getLogin());
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

    protected function logLoginHistory(): void
    {
        $user = $this->getLoginUser();
        $loginHistoryModel = new LoginHistoryModel();
        $loginDetails = LoginDetails::getLoginDetails();
        $loginHistoryModel->setLoginDate($loginDetails['loginTime']);
        $loginHistoryModel->setIp($loginDetails['ipAddress']);
        $loginHistoryModel->setDevice($loginDetails['deviceLogin']);
        $loginHistoryModel->setSession($loginDetails['sessionId']);
        $loginHistoryModel->setUser($user);

        $this->loginHistoryService->addLoginLog($loginHistoryModel);
    }

}