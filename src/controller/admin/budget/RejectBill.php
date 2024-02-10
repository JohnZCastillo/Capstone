<?php

namespace App\controller\admin\budget;

use App\controller\admin\AdminAction;
use App\exception\fund\ExpenseNotFound;
use App\model\enum\BudgetStatus;
use App\model\enum\LogsTag;
use Exception;
use Psr\Http\Message\ResponseInterface as Response;

class RejectBill extends AdminAction
{

    protected function action(): Response
    {

        try {

            $id = $this->args['id'];

            $expense = $this->expenseService->findById($id);

            $expense->setStatus(BudgetStatus::rejected());

            $this->expenseService->save($expense);

            $this->addActionLog("Bill with $id was rejected ",LogsTag::bill());

        } catch (ExpenseNotFound $expenseNotFound) {
            $this->addErrorMessage($expenseNotFound->getMessage());
        } catch (Exception $exception) {
            $this->addErrorMessage('An Internal Error Occurred');
        }

        return $this->redirect("/admin/budget");

    }
}