<?php

namespace App\controller\api\bill;

use App\controller\admin\AdminAction;
use App\exception\fund\BillLock;
use App\exception\fund\BillNotFound;
use App\model\budget\ExpenseModel;
use App\model\enum\BudgetStatus;
use Exception;
use Psr\Http\Message\ResponseInterface as Response;
use thiagoalessio\TesseractOCR\Tests\Common\TestCase;

class GenerateBill extends AdminAction
{

    /**
     * @inheritDoc
     */
    protected function action(): Response
    {


        try {

            $billId = $this->args['id'];


            $bill = $this->billService->findById($billId);

            if ($bill->isArchived()) {
                throw new BillLock("Bill is set to archived, please make the bill active first");
            }

            $expense = $bill->getExpense();

            $newExpenseBill = new ExpenseModel();
            $newExpenseBill->setStatus(BudgetStatus::pending());
            $newExpenseBill->setAmount($expense->getAmount());
            $newExpenseBill->setTitle($expense->getTitle());
            $newExpenseBill->setFund($expense->getFund());
            $newExpenseBill->setPurpose($expense->getPurpose());
            $newExpenseBill->setBill($bill);

            $this->expenseService->save($newExpenseBill);

        } catch (BillLock $billLock) {
            $this->addErrorMessage($billLock->getMessage());
        } catch (BillNotFound $billNotFound) {
            $this->addErrorMessage($billNotFound->getMessage());
        } catch (Exception $e) {
            $this->addErrorMessage('An Internal Error Occurred');
        }

        return $this->redirect('/admin/budget');
    }
}