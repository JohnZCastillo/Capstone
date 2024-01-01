<?php

namespace App\controller\admin\budget;

use App\controller\admin\AdminAction;
use App\exception\fund\FundNotFound;
use App\exception\fund\FundSourceNotFound;
use App\exception\fund\NegativeFund;
use App\exception\InvalidInput;
use App\model\budget\BillModel;
use App\model\budget\ExpenseModel;
use App\model\budget\FundModel;
use App\model\budget\IncomeModel;
use App\model\enum\BudgetStatus;
use DateTime;
use Exception;
use Psr\Http\Message\ResponseInterface as Response;
use Respect\Validation\Validator as v;

class TransferFund extends AdminAction
{

    protected function action(): Response
    {

        $content = $this->getFormData();
        $fundId = $this->args['id'];

        try {

            $fund = $this->fundService->findById($fundId);
            $fundDestination = $this->fundService->findById($content['fundTo']);

            if (!v::numericVal()->notEmpty()->positive()->validate($content['amount'])) {
                throw new InvalidInput('Invalid Amount');
            }


            if($fund->computeTotal() - ((float)$content['amount']) < 0){
                throw new NegativeFund('Cannot proceed transfer, insufficient balance.');
            }

            $expense = new ExpenseModel();
            $expense->setTitle("Transfer Fund " . $fundDestination->getTitle());
            $expense->setFund($fund);
            $expense->setAmount($content['amount']);
            $expense->setPurpose('FUND Transfer');
            $expense->setStatus(BudgetStatus::approved());

            $this->expenseService->save($expense);

            $fundSource = $this->fundSourceService->findByName('Transfer');

            $income = new IncomeModel();
            $income->setTitle("Received Fund from " . $fund->getTitle());
            $income->setAmount($content['amount']);
            $income->setFund($fundDestination);
            $income->setSource($fundSource);

            $this->incomeService->save($income);

        } catch (NegativeFund $negativeFund) {
            $this->addErrorMessage($negativeFund->getMessage());
        } catch (FundSourceNotFound $fundSourceNotFound) {
            $this->addErrorMessage($fundSourceNotFound->getMessage());
        } catch (InvalidInput $invalidInput) {
            $this->addErrorMessage($invalidInput->getMessage());
        } catch (FundNotFound $fundNotFound) {
            $this->addErrorMessage($fundNotFound->getMessage());
        } catch (Exception $exception) {
            $this->addErrorMessage('An Internal Error Occurred');
        }

        return $this->redirect("/admin/fund/$fundId");

    }
}