<?php

global $twig;

use Slim\App;
use Slim\Interfaces\RouteCollectorProxyInterface as Group;

return function (App $app) use ($twig) {

    $app->group('', function (Group $group) {

        $group->get('/monthly-collection/{id}/{year}',
            \App\controller\api\fund\MonthlyFund::class
        );

        $group->group('/admin', function (Group $group) {

            $group->get('/budget',
                \App\controller\admin\budget\BudgetManagement::class
            )->setName('budget');

            $group->post('/new-fund',
                \App\controller\admin\budget\AddFund::class
            );

            $group->get('/fund/{id}',
                \App\controller\admin\budget\ViewFund::class
            );

            $group->post('/fund/archive/{id}',
                \App\controller\admin\budget\ArchiveFund::class
            );

            $group->post('/fund/unarchived/{id}',
                \App\controller\admin\budget\UnarchiveFund::class
            );

            $group->post('/new-bill',
                \App\controller\admin\budget\AddBill::class
            );

            $group->post('/edit-bill',
                \App\controller\admin\budget\EditBill::class
            );

            $group->post('/archive-bill/{id}',
                \App\controller\admin\budget\ArchiveBill::class
            );

            $group->post('/approve-bill/{id}',
                \App\controller\admin\budget\ApproveBill::class
            );

            $group->post('/approve-expense/{id}',
                \App\controller\admin\budget\ApproveExpense::class
            );

            $group->post('/reject-expense/{id}',
                \App\controller\admin\budget\RejectExpense::class
            );

            $group->get('/fund/expenses/{id}',
                \App\controller\admin\budget\ViewFundExpenses::class
            );

            $group->get('/fund/incomes/{id}',
                \App\controller\admin\budget\ViewFundIncomes::class
            );

            $group->post('/transfer/{id}',
                \App\controller\admin\budget\TransferFund::class
            );

            $group->get('/fund/report/{id}',
                \App\controller\admin\budget\FundReport::class
            );

            $group->get('/fund/report/print/{id}',
                \App\controller\admin\report\PrintBudgetReport::class
            );

            $group->post('/reject-bill/{id}',
                \App\controller\admin\budget\RejectBill::class
            );

            $group->post('/unarchive-bill/{id}',
                \App\controller\admin\budget\UnarchiveBill::class
            );

            $group->get('/bill/{id}',
                \App\controller\api\bill\FindBill::class
            );

            $group->post('/bill/generate/{id}',
                \App\controller\api\bill\GenerateBill::class
            );

            $group->post('/add-expense/{id}',
                \App\controller\admin\budget\AddExpense::class
            );

            $group->post('/add-income/{id}',
                \App\controller\admin\budget\AddIncome::class
            );

        })
            ->add(\App\middleware\ActivePage::class);

    })->add(\App\middleware\access\AdminPayments::class)
        ->add(\App\middleware\role\AdminAuth::class)
        ->add(\App\middleware\Auth::class);
};