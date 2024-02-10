<?php

namespace App\controller\admin\budget;

use App\controller\admin\AdminAction;
use App\exception\fund\FundNotFound;
use App\model\enum\LogsTag;
use Exception;
use Psr\Http\Message\ResponseInterface as Response;

class UnarchiveFund extends AdminAction
{

    protected function action(): Response
    {

        $id = $this->args['id'];

        try {
            $fund = $this->fundService->findById($id);

            $fund->setIsArchived(false);

            $this->fundService->save($fund);

            $fundName = $fund->getTitle();

            $this->addActionLog("Fund with id of $id was unarchived ",LogsTag::fund());

            $this->addSuccessMessage("$fundName unarchived successfully");
        } catch (FundNotFound $fundNotFound) {
            $this->addErrorMessage($fundNotFound->getMessage());
        } catch (Exception $exception) {
            $this->addErrorMessage('An Internal Error Occurred');
        }
        return $this->redirect("/admin/budget");
    }
}