<?php

namespace App\controller\admin\budget;

use App\controller\admin\AdminAction;
use App\exception\fund\ExpenseNotFound;
use App\exception\fund\FundNotFound;
use App\exception\fund\NegativeFund;
use App\exception\InvalidInput;
use App\model\budget\BillModel;
use App\model\budget\ExpenseModel;
use App\model\budget\FundModel;
use App\model\enum\BudgetStatus;
use DateTime;
use Exception;
use Psr\Http\Message\ResponseInterface as Response;
use Respect\Validation\Validator as v;

class ApproveBill extends AdminAction
{

    protected function action(): Response
    {

        try {

            $id = $this->args['id'];

            $expense = $this->expenseService->findById($id);

            if ($expense->getFund()->computeTotal() - $expense->getAmount() < 0) {
                throw new NegativeFund("Insufficient funds available for this expense.");
            }

            $expense->setStatus(BudgetStatus::approved());

            $this->expenseService->save($expense);

        } catch (NegativeFund $negativeFund) {
            $this->addErrorMessage($negativeFund->getMessage());
        }  catch (ExpenseNotFound $expenseNotFound) {
            $this->addErrorMessage($expenseNotFound->getMessage());
        } catch (Exception $exception) {
            $this->addErrorMessage('An Internal Error Occurred');
        }

        return $this->redirect("/admin/budget");

    }
}