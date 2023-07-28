<?php

namespace App\controller;

use App\lib\Helper;
use App\lib\Login;
use App\model\AnnouncementModel;
use App\model\PaymentModel;
use App\model\UserModel;
use App\service\AnnouncementService;
use App\service\DuesService;
use App\service\IssuesService;
use App\service\PaymentService;
use App\service\ReceiptService;
use App\service\TransactionLogsService;
use App\service\UserService;
use App\service\TransactionService;
use Slim\Flash\Messages;
use UMA\DIC\Container;

class Controller {

    protected UserService $userSerivce;
    protected TransactionService $transactionService;
    protected DuesService $duesService;
    protected ReceiptService $receiptService;
    protected PaymentService $paymentService;
    protected TransactionLogsService $logsService;
    protected AnnouncementService $announcementService;
    protected Messages $flashMessages;
    protected IssuesService $issuesService;

    public function __construct(Container  $container) {
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
    }

    protected function getLogin():UserModel{
        return $this->userSerivce->findById(Login::getLogin());
    }

    //return default payment settings
    protected function getPaymentSettings():PaymentModel{
        $id = 1;
        return $this->paymentService->findById($id);
    }

    /**
     * Wrapper function to get unpaid due for the month.
     * user to find balance. (Default: login user).
     */
    protected function getBalance($month,$user = null){
        // if user null then set to login user otherwise passed user
        $user = Helper::getValue($user,$this->getLogin());

        // dues service
        $dues = $this->duesService;

        //return the balance of the user for the month
        return $this->transactionService->getBalance($user, $month, $dues);
    }
    
     /**
     * Wrapper function to get total dues of user for the unpaid months
     * @return float total dues
     */
    protected function getTotalDues($user = null){

        // if user null then set to login user otherwise passed user
        $user = Helper::getValue($user,$this->getLogin());

        // dues service
        $dues = $this->duesService;

        $paymentSettings = $this->getPaymentSettings();

        //return the balance of the user for the month
        return $this->transactionService->getUnpaid($user,$dues,$paymentSettings)['total'];
    }



}