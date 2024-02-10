<?php

namespace App\controller\admin\budget;

use App\controller\admin\AdminAction;
use App\exception\fund\BillNotFound;
use App\exception\InvalidInput;
use App\model\enum\LogsTag;
use Exception;
use Psr\Http\Message\ResponseInterface as Response;

class UnarchiveBill extends AdminAction
{

    protected function action(): Response
    {

        $content = $this->getFormData();

        try {

            $id = $this->args['id'];

            $bill = $this->billService->findById($id);

            $bill->setIsArchived(false);
            $this->billService->save($bill);

            $billName = $bill->getExpense()->getTitle();

            $this->addActionLog("Bill with id of $id was archived ",LogsTag::bill());

            $this->addSuccessMessage("$billName unarchived successfully");
            
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