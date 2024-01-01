<?php

global $twig;

use Slim\App;
use Slim\Interfaces\RouteCollectorProxyInterface as Group;

return function (App $app) use ($twig) {

    $app->group('/admin', function (Group $group) {

        $group->get('/account',
            \App\controller\admin\account\Account::class
        )->setName('account');


    })->add(\App\middleware\role\AdminAuth::class)
        ->add(\App\middleware\Auth::class);

};