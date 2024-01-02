<?php

namespace App\controller\api\payments;

use App\controller\admin\AdminAction;
use App\exception\date\InvalidDateFormat;
use App\lib\Time;
use Doctrine\DBAL\Driver\Exception;
use Psr\Http\Message\ResponseInterface as Response;

class DueAmount extends AdminAction
{

    /**
     * @inheritDoc
     */
    protected function action(): Response
    {

        try {

            $body = $this->getFormData();

            $fromMonth = $body['fromMonth'];
            $toMonth = $body['toMonth'];

            $fromMonth = Time::convertToString(Time::startMonth($fromMonth));
            $toMonth =  Time::convertToString(Time::startMonth($toMonth));

            $user = $this->getLoginUser();

            $amount = $this->transactionService->getUnpaid(
                $user,
                $this->duesService,
                $this->paymentService->findById(1),
                $fromMonth,
                $toMonth
            );

            return $this->respondWithData(['amount' => $amount['total']]);

        }catch (InvalidDateFormat $e) {
            return $this->respondWithData(['message' => $e->getMessage()],400);
        } catch (Exception $e){
            return $this->respondWithData(['message' => 'Something went wrong'],500);
        }

    }
}