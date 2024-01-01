<?php

namespace App\controller\admin\budget;

use App\controller\admin\AdminAction;
use App\exception\fund\BillNotFound;
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

class ArchiveBill extends AdminAction
{

    protected function action(): Response
    {

        $content = $this->getFormData();

        try {

            $id = $this->args['id'];

            $bill = $this->billService->findById($id);

            $bill->setIsArchived(true);
            $this->billService->save($bill);

            $billName = $bill->getExpense()->getTitle();

            $this->addSuccessMessage("$billName archived successfully");

        } catch (InvalidInput $invalidInput) {
            $this->addErrorMessage($invalidInput->getMessage());
        } catch (BillNotFound $billNotFound) {
            $this->addErrorMessage($billNotFound->getMessage());
        } catch (Exception $exception) {
            $this->addErrorMessage('An Internal Error Occurred');
        }

        return $this->redirect("/admin/budget");

    }
}