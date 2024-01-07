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

class ArchiveFund extends AdminAction
{

    protected function action(): Response
    {

        $id = $this->args['id'];

        try {
            $fund = $this->fundService->findById($id);

            if($fund->isMainFund()){
                throw new FundNotFound('Cannot Archive Main Fund');
            }

            $fund->setIsArchived(true);

            $this->fundService->save($fund);

            $fundName = $fund->getTitle();

            $this->addActionLog("Fund with $id was archived ",LogsTag::fund());

            $this->addSuccessMessage("$fundName archived successfully");
        }catch (FundNotFound $fundNotFound){
            $this->addErrorMessage($fundNotFound->getMessage());
        }catch (Exception $exception){
            $this->addErrorMessage('An Internal Error Occurred');
        }
        return $this->redirect("/admin/budget");
    }
}