<?php

global $twig;

use Slim\App;
use Slim\Interfaces\RouteCollectorProxyInterface as Group;

return function (App $app) use ($twig) {

    $app->group('', function (Group $group) {

        $group->post('/payable-amount',
            \App\controller\api\payments\DueAmount::class
        );

        $group->get('/home',
            \App\controller\user\payments\ViewHomepage::class
        )->setName('home');

        $group->get('/transaction/{id}',
            \App\controller\user\payments\Transaction::class
        )->setName('home');

        $group->get('/dues',
            \App\controller\user\payments\UnpaidDues::class
        )->setName('home');

        $group->post('/pay',
            \App\controller\user\payments\Pay::class
        )->setName('home');

        $group->get('/announcements',
            \App\controller\user\announcements\Announcements::class
        );

        $group->post('/issue',
            \App\controller\user\issues\CreateIssue::class
        );

        $group->post('/issue/archive/{id}',
            \App\controller\user\issues\ArchiveIssue::class
        );

        $group->post('/issue/unarchive/{id}',
            \App\controller\user\issues\PostIssue::class
        );

        $group->get('/issues',
            \App\controller\user\issues\ViewIssues::class
        );

        $group->get('/issues/{id}',
            \App\controller\user\issues\ViewIssue::class
        );

        $group->get('/account',
            \App\controller\user\account\Account::class
        );


    })->add(\App\middleware\ActivePage::class)
        ->add(\App\middleware\role\UserAuth::class)
        ->add(\App\middleware\Auth::class);
};