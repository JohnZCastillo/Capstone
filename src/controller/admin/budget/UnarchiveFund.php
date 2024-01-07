<?php

namespace App\controller\admin\budget;

use App\controller\admin\AdminAction;
use App\exception\fund\FundNotFound;
use App\exception\InvalidInput;
use App\model\budget\FundModel;
use App\model\enum\LogsTag;
use DateTime;
use Exception;
use Psr\Http\Message\ResponseInterface as Response;
use Respect\Validation\Validator as v;

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