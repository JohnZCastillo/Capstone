<?php

namespace App\service;

use App\exception\payment\TransactionNotFound;
use App\lib\Paginator;
use App\lib\Time;
use App\model\MonthlyPaymentModel;
use App\model\PaymentModel;
use App\model\TransactionModel;
use App\model\UserModel;
use DateTime;
use DoctrineExtensions\Query\Mysql\IfElse;

class MonthlyPaymentService extends Service
{

    public function save(MonthlyPaymentModel $monthlyPaymentModel): void
    {
        $this->entityManager->persist($monthlyPaymentModel);
        $this->entityManager->flush($monthlyPaymentModel);
    }

    public function isPaid(UserModel $userModel): bool
    {

        return  false;
    }

}
