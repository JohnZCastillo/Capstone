<?php

namespace App\controller\api\payments;

use App\controller\admin\AdminAction;
use App\lib\Time;
use Psr\Http\Message\ResponseInterface as Response;

class DueAmount extends AdminAction
{

    /**
     * @inheritDoc
     */
    protected function action(): Response
    {

        $body = $this->getFormData();

        $fromMonth = $body['fromMonth'];
        $toMonth = $body['toMonth'];

        $fromMonth = Time::nowStartMonth($fromMonth);
        $toMonth = Time::nowStartMonth($toMonth);

        $user = $this->getLoginUser();

        $amount = $this->transactionService->getUnpaid(
            $user,
            $this->duesService,
            $this->paymentService->findById(1),
            $fromMonth,
            $toMonth
        );

        return $this->respondWithData(['amount' => $amount['total']]);
    }
}