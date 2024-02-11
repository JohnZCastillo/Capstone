<?php

declare(strict_types=1);

namespace App\controller\admin\payments;

use App\controller\admin\AdminAction;
use Carbon\Carbon;
use Exception;
use Psr\Http\Message\ResponseInterface as Response;

class UnitOverview extends AdminAction
{
    protected function action(): Response
    {

            $userId = $this->args['id'];
            $transactionId = $this->args['transaction'];

        try {

            $user = $this->userService->findById($userId);

            $endMonth = Carbon::now();
            $endMonth->setDay(1);

            $startCollection = $this->getCollectionStartDate();

            $unpaidDue = $this->transactionService->getUnpaid(
                $user,
                $this->duesService,
                $this->paymentService->findById(1),
                $startCollection->format('Y-m-d'),
                $endMonth->format('Y-m-d')
            );

            $unpaidDue['transactionId'] =  $transactionId;

            return $this->view('admin/pages/unit-overview.html', $unpaidDue);

        }catch (Exception $exception){
            $this->addErrorMessage($exception->getMessage());
        }

        return $this->redirect('/admin/transaction/' . $transactionId);
    }
}