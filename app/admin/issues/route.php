<?php

global $twig;

use Slim\App;
use Slim\Interfaces\RouteCollectorProxyInterface as Group;

return function (App $app) use ($twig) {

    $app->group('/admin', function (Group $group) {

        $group->get('/issues',
            \App\controller\admin\issues\Issues::class
        )->setName('issues');

        $group->get('/issue/{id}',
            \App\controller\admin\issues\Issue::class
        )->setName('issues');

        $group->post('/issues/action',
            \App\controller\admin\issues\MakeAction::class
        )->setName('issues');

    })->add(\App\middleware\access\AdminIssues::class)
        ->add(\App\middleware\role\AdminAuth::class)
        ->add(\App\middleware\Auth::class)
        ->add(\App\middleware\ActivePage::class);

};