<?php

global $twig;

use Slim\App;
use Slim\Interfaces\RouteCollectorProxyInterface as Group;

return function (App $app) use ($twig) {

    $app->group('', function (Group $group) {

        $group->post('/change-password',
            \App\controller\api\UpdatePassword::class
        );

        $group->post('/change-details',
            \App\controller\api\users\UpdateAccountDetails::class
        );

        $group->post('/force-logout',
            \App\controller\api\users\ForceLogout::class
        );

        $group->group('/admin', function (Group $group) {

            $group->get('/account',
                \App\controller\admin\account\Account::class
            )->setName('account');


        })->add(\App\middleware\ActivePage::class);

    })->add(\App\middleware\role\AdminAuth::class)
        ->add(\App\middleware\Auth::class);

};