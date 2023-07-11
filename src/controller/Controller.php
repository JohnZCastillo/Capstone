<?php

namespace App\controller;

use App\lib\Login;
use App\model\UserModel;
use App\service\DuesService;
use App\service\PaymentService;
use App\service\ReceiptService;
use App\service\TransactionLogsService;
use App\service\UserService;
use App\service\TransactionService;
use UMA\DIC\Container;

class Controller {

    protected UserService $userSerivce;
    protected TransactionService $transactionService;
    protected DuesService $duesService;
    protected ReceiptService $receiptService;
    protected PaymentService $paymentService;
    protected TransactionLogsService $logsService;

    public function __construct(Container  $container) {
        //get the userService from dependency container
        $this->userSerivce = $container->get(UserService::class);
        $this->transactionService = $container->get(TransactionService::class);
        $this->duesService = $container->get(DuesService::class);
        $this->receiptService = $container->get(ReceiptService::class);
        $this->paymentService = $container->get(PaymentService::class);
        $this->logsService = $container->get(TransactionLogsService::class);
    }

    protected function getLogin():UserModel{
        return $this->userSerivce->findById(Login::getLogin());
    }

}
