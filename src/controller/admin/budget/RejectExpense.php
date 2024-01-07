<?php

namespace App\controller\admin\budget;

use App\controller\admin\AdminAction;
use App\exception\fund\ExpenseNotFound;
use App\exception\fund\FundNotFound;
use App\exception\InvalidInput;
use App\model\budget\BillModel;
use App\model\budget\ExpenseModel;
use App\model\budget\FundModel;
use App\model\enum\BudgetStatus;
use App\model\enum\LogsTag;
use DateTime;
use Exception;
use Psr\Http\Message\ResponseInterface as Response;
use Respect\Validation\Validator as v;

class RejectExpense extends AdminAction
{

    protected function action(): Response
    {

        $id = $this->args['id'];

        $fundId = 0;

        try {
            $expense = $this->expenseService->findById($id);

            $fundId = $expense->getFund()->getId();

            $expense->setStatus(BudgetStatus::rejected());

            $this->expenseService->save($expense);

            $this->addActionLog("Expense with $id was rejected ",LogsTag::expense());

        } catch (ExpenseNotFound $expenseNotFound) {
            $this->addErrorMessage($expenseNotFound->getMessage());
            return $this->redirect("/admin/budget");
        } catch (Exception $exception) {
            $this->addErrorMessage('An Internal Error Occurred');
        }

        return $this->redirect("/admin/fund/$fundId");

    }
}