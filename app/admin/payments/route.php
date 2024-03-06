<?php

global $twig;

use Slim\App;
use Slim\Interfaces\RouteCollectorProxyInterface as Group;

return function (App $app) use ($twig) {

    $app->group('/admin', function (Group $group) {

        $group->get('/payments', \App\controller\admin\payments\Homepage::class)
            ->setName('home');

        $group->post('/payments/add-due',
            \App\controller\admin\payments\AddDue::class
        );

        $group->post('/report',
            \App\controller\admin\report\PaymentReport::class
        );

        $group->post('/payments/year-dues',
            \App\controller\admin\payments\YearlyDue::class
        );

        $group->post('/payments/manual',
            \App\controller\admin\payments\ManualPayment::class
        );

        $group->post('/payment-settings',
            \App\controller\admin\payments\PaymentSettings::class
        );

        $group->post('/transaction/pending/{id}',
            \App\controller\admin\payments\PendingPayment::class);

        $group->post('/transaction/approve',
            \App\controller\admin\payments\ApprovePayment::class)
            ->setName('home');

        $group->post('/transaction/reject',
            \App\controller\admin\payments\RejectPayment::class
        )->setName('home');

        $group->get('/transaction/{id}',
            \App\controller\admin\payments\Transaction::class
        )->setName('home');

        $group->get('/unit-overview/{transaction}/{id}',
            \App\controller\admin\payments\UnitOverview::class
        )->setName('home');

        $group->post('/add-due',
        \App\controller\admin\payments\AddDue::class
        );

        $group->post('/year-dues',
            \App\controller\admin\payments\YearlyDue::class
        );

    })->add(\App\middleware\access\AdminPayments::class)
        ->add(\App\middleware\role\AdminAuth::class)
        ->add(\App\middleware\Auth::class)
        ->add(\App\middleware\ActivePage::class);
};