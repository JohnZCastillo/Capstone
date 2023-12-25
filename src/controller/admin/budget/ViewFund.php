<?php

namespace App\controller\admin\budget;

use App\controller\admin\AdminAction;
use App\exception\fund\FundNotFound;
use DateTime;
use Exception;
use Psr\Http\Message\ResponseInterface as Response;

class ViewFund extends AdminAction
{

    protected function action(): Response
    {
        try {

            $formData = $this->getFormData();

            $id = $this->args['id'];

            $fund = $this->fundService->findById($id);
            $fundSources = $this->fundSourceService->getAll();

            $year = (new DateTime())->format('Y');

            $expenses = $this->fundService->getYearlyExpenses($fund->getId(), $year);
            $incomes = $this->fundService->getYearlyIncome($fund->getId(), $year);
            $keys = $this->fundService->getKeys($year);

            return $this->view('admin/pages/fund-details.html', [
                'fund' => $fund,
                'fundSources' => $fundSources,
                'yearlyExpenses' => array_values($expenses),
                'yearlyIncomes' => array_values($incomes),
                'keys' => $keys,
            ]);

        } catch (FundNotFound $fundNotFound) {
            $this->addErrorMessage($fundNotFound->getMessage());
        } catch (Exception $e) {
            $this->addErrorMessage("An Internal Error Occurred");
        }

        return $this->redirect("/admin/budget");
    }
}