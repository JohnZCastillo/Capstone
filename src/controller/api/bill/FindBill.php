<?php

namespace App\controller\api\bill;

use App\controller\admin\AdminAction;
use App\exception\fund\BillNotFound;
use Exception;
use Psr\Http\Message\ResponseInterface as Response;

class FindBill extends AdminAction
{

    /**
     * @inheritDoc
     */
    protected function action(): Response
    {


        try {

            $billId = $this->args['id'];

            $bill = $this->billService->findById($billId);

            return $this->respondWithData([
                'id' => $bill->getId(),
                'title' => $bill->getExpense()->getTitle(),
                'amount' => $bill->getExpense()->getAmount(),
                'purpose' => $bill->getExpense()->getPurpose(),
                'interval' => 'test',
                'fundId' => $bill->getExpense()->getFund()->getId(),
                'fundName' => $bill->getExpense()->getFund()->getTitle(),
            ]);

        } catch ( BillNotFound $billNotFound) {
            return $this->respondWithData(['message' => $billNotFound->getMessage()],404);
        }catch (Exception $e) {
            return $this->respondWithData(['message' => 'An Internal Error Occurred'],500);
        }

    }
}