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

class EditBill extends AdminAction
{

    protected function action(): Response
    {

        $content = $this->getFormData();

        try {

            $formData = $this->getFormData();

            $fund = $this->fundService->findById($content['fund']);
            $bill = $this->billService->findById($content['billId']);

            if (!v::alnum(' ')->notEmpty()->validate($content['title'])) {
                throw new InvalidInput('Invalid Fund Title');
            }

            if (!v::alnum(' ')->notEmpty()->validate($content['purpose'])) {
                throw new InvalidInput('Invalid Purpose content');
            }

            if (!v::numericVal()->notEmpty()->validate($content['amount'])) {
                throw new InvalidInput('Invalid Amount');
            }

            $expense = $bill->getExpense();
            $expense->setTitle($content['title']);
            $expense->setFund($fund);
            $expense->setAmount($content['amount']);
            $expense->setPurpose($content['purpose']);

            $this->expenseService->save($expense);

        } catch (InvalidInput $invalidInput) {
            $this->addErrorMessage($invalidInput->getMessage());
        } catch (FundNotFound $fundNotFound) {
            $this->addErrorMessage($fundNotFound->getMessage());
        } catch (Exception $exception) {
            $this->addErrorMessage('An Internal Error Occurred');
        }

        return $this->redirect("/admin/budget");

    }
}