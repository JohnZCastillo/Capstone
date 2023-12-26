<?php

namespace App\controller\admin\budget;

use App\controller\admin\AdminAction;
use App\exception\fund\FundNotFound;
use App\exception\InvalidInput;
use App\model\budget\BillModel;
use App\model\budget\ExpenseModel;
use App\model\budget\FundModel;
use App\model\enum\BudgetStatus;
use DateTime;
use Exception;
use Psr\Http\Message\ResponseInterface as Response;
use Respect\Validation\Validator as v;

class AddExpense extends AdminAction
{

    protected function action(): Response
    {

        $content = $this->getFormData();
        $id = $this->args['id'];

        try {

            $fund = $this->fundService->findById($id);

            if(!v::alnum(' ')->notEmpty()->validate($content['title'])){
                throw new InvalidInput('Invalid Fund Title');
            }

            if(!v::alnum(' ')->notEmpty()->validate($content['purpose'])){
                throw new InvalidInput('Invalid Purpose content');
            }

            if(!v::numericVal()->notEmpty()->positive()->validate($content['amount'])){
                throw new InvalidInput('Invalid Amount');
            }

            $expense = new ExpenseModel();
            $expense->setTitle($content['title']);
            $expense->setFund($fund);
            $expense->setAmount($content['amount']);
            $expense->setPurpose($content['purpose']);

            $this->expenseService->save($expense);

            return $this->redirect("/admin/fund/$id");

        }  catch (InvalidInput $invalidInput) {
            $this->addErrorMessage($invalidInput->getMessage());
            return $this->redirect("/admin/fund/$id");
        } catch (FundNotFound $fundNotFound) {
            $this->addErrorMessage($fundNotFound->getMessage());
        } catch (Exception $exception) {
            $this->addErrorMessage('An Internal Error Occurred');
        }

        return $this->redirect("/admin/budget");
    }
}