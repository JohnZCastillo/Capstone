<?php

declare(strict_types=1);

namespace App\controller\user;

use App\controller\Action;
use App\lib\Login;
use App\lib\LoginDetails;
use App\model\LoginHistoryModel;
use App\model\LogsModel;
use App\model\UserModel;
use App\service\LoginHistoryService;
use App\service\LogsService;
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

    /**
     * @param UserService $userService
     * @param Messages $flashMessage
     * @param LogsService $logsService
     * @param LoginHistoryService $loginHistoryService
     * @param TransactionService $transactionService
     */
    public function __construct(UserService $userService, Messages $flashMessage, LogsService $logsService, LoginHistoryService $loginHistoryService, TransactionService $transactionService)
    {
        $this->userService = $userService;
        $this->flashMessage = $flashMessage;
        $this->logsService = $logsService;
        $this->loginHistoryService = $loginHistoryService;
        $this->transactionService = $transactionService;
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