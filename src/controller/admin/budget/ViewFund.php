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

            $funds = $this->fundService->getAll();

            $recentIncomes = $this->incomeService->getRecentIncome(5);
            $recentExpenses = $this->expenseService->getRecentIncome(5);

            return $this->view('admin/pages/fund-details.html', [
                'funds' => $funds,
                'fund' => $fund,
                'currentFund' => $fund->getId(),
                'fundSources' => $fundSources,
                'yearlyExpenses' => array_values($expenses),
                'yearlyIncomes' => array_values($incomes),
                'keys' => $keys,
                'recentIncomes' => $recentIncomes,
                'recentExpenses' => $recentExpenses,
            ]);

        } catch (FundNotFound $fundNotFound) {
            $this->addErrorMessage($fundNotFound->getMessage());
        } catch (Exception $e) {
            $this->addErrorMessage("An Internal Error Occurred");
        }

        return $this->redirect("/admin/budget");
    }
}