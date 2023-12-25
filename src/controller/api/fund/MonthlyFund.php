<?php

namespace App\controller\api\fund;

use App\controller\admin\AdminAction;
use Psr\Http\Message\ResponseInterface as Response;

class MonthlyFund extends AdminAction
{

    /**
     * @inheritDoc
     */
    protected function action(): Response
    {
        try {

            $fundId = (int)$this->args['id'];
            $year = (int)$this->args['year'];

            $tally = $this->fundService->getMonthlyTally($fundId, $year);

            return $this->respondWithData($tally);
        } catch (\Exception $e) {
            return $this->respondWithData(['message' => 'Internal Error Occurred'], 500);
        }
    }
}