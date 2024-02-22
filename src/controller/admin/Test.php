<?php

namespace App\controller\admin;

use App\controller\admin\AdminAction;
use App\lib\MonthlyPaymentHelper;
use App\lib\ReferenceExtractor;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Carbon\Month;
use Psr\Http\Message\ResponseInterface as Response;

class Test extends AdminAction
{

    /**
     * @inheritDoc
     */
    protected function action(): Response
    {

//        $now = Carbon::now();
////        $transactionDate = Carbon::createFromFormat('Y-m-d','2024-04-01');
////
////        $difference = $transactionDate->diffInDays($now);
//
//        $test = $this->logsService->test($now->toDateTime());

        $transaction = $this->transactionService->findById(23);

        $count = count($this->transactionService->getByApprovedReferences($transaction,['7014500752897']));


        return $this->respondWithData(['message' => $count]);

    }
}