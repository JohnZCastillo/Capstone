<?php

namespace App\controller\admin\budget;

use App\controller\admin\AdminAction;
use App\exception\fund\ExpenseNotFound;
use App\exception\fund\NegativeFund;
use App\model\enum\BudgetStatus;
use App\model\enum\LogsTag;
use Exception;
use Psr\Http\Message\ResponseInterface as Response;
use Respect\Validation\Validator as v;

class ApproveExpense extends AdminAction
{

    protected function action(): Response
    {

        $id = $this->args['id'];

        $fundId = 0;

        try {
            $expense = $this->expenseService->findById($id);

            $fundId = $expense->getFund()->getId();

            if ($expense->getFund()->computeTotal() - $expense->getAmount() < 0) {
                throw new NegativeFund("Insufficient funds available for this expense.");
            }

            $expense->setStatus(BudgetStatus::approved());

            $this->addActionLog("Expense with $id was approved ",LogsTag::expense());

            $this->expenseService->save($expense);

        } catch (NegativeFund $negativeFund) {
            $this->addErrorMessage($negativeFund->getMessage());
        } catch (ExpenseNotFound $expenseNotFound) {
            $this->addErrorMessage($expenseNotFound->getMessage());
            return $this->redirect("/admin/budget");
        } catch (Exception $exception) {
            $this->addErrorMessage('An Internal Error Occurred');
        }

        return $this->redirect("/admin/fund/$fundId");

    }
}