<?php

namespace App\controller;

use App\lib\Helper;
use App\lib\Login;
use App\lib\Time;
use App\model\PaymentModel;
use App\model\UserLogsModel;
use App\model\UserModel;
use App\service\AnnouncementService;
use App\service\CodeModelService;
use App\service\DuesService;
use App\service\IssuesService;
use App\service\LoginHistoryService;
use App\service\LogsService;
use App\service\PaymentService;
use App\service\PriviligesService;
use App\service\ReceiptService;
use App\service\TransactionLogsService;
use App\service\TransactionService;
use App\service\UserLogsService;
use App\service\UserService;
use Slim\Flash\Messages;
use UMA\DIC\Container;

class Controller
{

    protected UserService $userSerivce;
    protected TransactionService $transactionService;
    protected DuesService $duesService;
    protected ReceiptService $receiptService;
    protected PaymentService $paymentService;
    protected TransactionLogsService $logsService;
    protected AnnouncementService $announcementService;
    protected Messages $flashMessages;
    protected IssuesService $issuesService;

    protected LoginHistoryService $loginHistoryService;
    protected PriviligesService $priviligesService;

    protected CodeModelService $codeModelService;

    protected UserLogsService $userLogsService;


    protected LogsService $actionLogs;

    public function __construct(Container $container)
    {
        //get the userService from dependency container
        $this->userSerivce = $container->get(UserService::class);
        $this->transactionService = $container->get(TransactionService::class);
        $this->duesService = $container->get(DuesService::class);
        $this->receiptService = $container->get(ReceiptService::class);
        $this->paymentService = $container->get(PaymentService::class);
        $this->logsService = $container->get(TransactionLogsService::class);
        $this->announcementService = $container->get(AnnouncementService::class);
        $this->issuesService = $container->get(IssuesService::class);
        $this->flashMessages = $container->get(Messages::class);
        $this->loginHistoryService = $container->get(LoginHistoryService::class);
        $this->priviligesService = $container->get(PriviligesService::class);
        $this->actionLogs = $container->get(LogsService::class);
        $this->codeModelService = $container->get(CodeModelService::class);
        $this->userLogsService = $container->get(UserLogsService::class);
    }

    protected function getLogin(): UserModel
    {
        return $this->userSerivce->findById(Login::getLogin());
    }

    //return default payment settings
    protected function getPaymentSettings(): PaymentModel|null
    {
        $id = 1;
        return $this->paymentService->findById($id);
    }


    /**
     * Wrapper function to get unpaid due for the month.
     * user to find balance. (Default: login user).
     */
    protected function getBalance($month, $user = null)
    {
        // if user null then set to login user otherwise passed user
        $user = Helper::getValue($user, $this->getLogin());

        // dues service
        $dues = $this->duesService;

        //return the balance of the user for the month
        return $this->transactionService->getBalance($user, $month, $dues);
    }

    /**
     * Wrapper function to get total dues of user for the unpaid months
     * @return float total dues
     */
    protected function getTotalDues($user = null)
    {

        // if user null then set to login user otherwise passed user
        $user = Helper::getValue($user, $this->getLogin());

        // dues service
        $dues = $this->duesService;

        $paymentSettings = $this->getPaymentSettings();

        //return the balance of the user for the month
        return $this->transactionService->getUnpaid($user, $dues, $paymentSettings)['total'];
    }

    /**
     * Add Message to the flashMessages.
     * Note: this doest show flashmessage on the view.
     */
    protected function flashMessage(string $key, string $message): void
    {
        $this->flashMessages->addMessage($key, $message);
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

    public function saveUserLog($action, $user)
    {
        $userLog = new UserLogsModel();

        $userLog->setUser($user);
        $userLog->setAction($action);
        $userLog->setCreatedAt(new \DateTime());

        $this->userLogsService->addLog($userLog);
    }

}