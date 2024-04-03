<?php

declare(strict_types=1);

namespace App\controller\admin\project;

use App\controller\admin\AdminAction;
use App\lib\Image;
use App\model\budget\ExpenseModel;
use App\model\budget\ProjectExpenseModel;
use App\model\budget\ProjectExpenseProofModel;
use App\model\enum\BudgetStatus;
use Exception;
use Psr\Http\Message\ResponseInterface as Response;

class NewExpense extends AdminAction
{
    /**
     * {@inheritdoc}
     */
    protected function action(): Response
    {
        try {

            $id = $this->args['id'];

            $content = $this->getFormData();

            $project = $this->projectService->getProjectById($id);

            $expense = new ProjectExpenseModel();
            $expense->setTitle($content['title']);
            $expense->setAmount($content['amount']);
            $expense->setProject($project);

            $this->projectService->saveProjectExpense($expense);

            $proof = new ProjectExpenseProofModel();
            $proof->setProjectExpense($expense);
            $proof->setImage(Image::store('./uploads/', $_FILES['proof']));

            $this->projectService->saveProjectExpenseProof($proof);

            $budgetExpense = new ExpenseModel();

            $budgetExpense->setTitle('Project');
            $budgetExpense->setAmount($expense->getAmount());
            $budgetExpense->setFund($this->fundService->findById(1));
            $budgetExpense->setPurpose($expense->getTitle());
            $budgetExpense->setStatus(BudgetStatus::approved());

            $this->expenseService->save($budgetExpense);

            return  $this->redirect("/admin/project/$id");

        } catch (Exception $exception) {
            return $this->respondWithData(['message' => $exception->getMessage()],500);
        }

    }
}