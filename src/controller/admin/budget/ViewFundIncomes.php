<?php

namespace App\controller\admin\budget;

use App\controller\admin\AdminAction;
use App\exception\fund\FundNotFound;
use Exception;
use Psr\Http\Message\ResponseInterface as Response;

class ViewFundIncomes extends AdminAction
{

    protected function action(): Response
    {
        try {


            $id = $this->args['id'];

            $fund = $this->fundService->findById($id);

            return $this->view('admin/pages/fund-incomes.html', [
                'fund' => $fund,
            ]);

        } catch (FundNotFound $fundNotFound) {
            $this->addErrorMessage($fundNotFound->getMessage());
        } catch (Exception $e) {
            $this->addErrorMessage("An Internal Error Occurred");
        }
        return $this->redirect("/admin/budget");
    }
}