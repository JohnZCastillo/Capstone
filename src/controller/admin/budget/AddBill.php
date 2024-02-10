<?php

namespace App\controller\admin\budget;

use App\controller\admin\AdminAction;
use App\exception\fund\FundNotFound;
use App\exception\InvalidInput;
use App\model\budget\BillModel;
use App\model\budget\ExpenseModel;
use App\model\enum\BudgetStatus;
use App\model\enum\LogsTag;
use Exception;
use Psr\Http\Message\ResponseInterface as Response;
use Respect\Validation\Validator as v;

class AddBill extends AdminAction
{

    protected function action(): Response
    {

        $content = $this->getFormData();

        try {

            $formData = $this->getFormData();

            $fund = $this->fundService->findById($content['fund']);

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
            $expense->setStatus(BudgetStatus::bill());

            $this->expenseService->save($expense);

            $bill = new BillModel();
            $bill->setExpense($expense);
            $this->billService->save($bill);

            $id = $bill->getId();

            $this->addActionLog("Bill with $id was created ",LogsTag::bill());


        }  catch (InvalidInput $invalidInput) {
            $this->addErrorMessage($invalidInput->getMessage());
        } catch (FundNotFound $fundNotFound) {
            $this->addErrorMessage($fundNotFound->getMessage());
        } catch (Exception $exception) {
            $this->addErrorMessage('An Internal Error Occurred');
        }

        return $this->redirect("/admin/budget");

    }
}