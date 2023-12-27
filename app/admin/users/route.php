<?php

global $twig;

use Slim\App;
use Slim\Interfaces\RouteCollectorProxyInterface as Group;

return function (App $app) use ($twig) {

    $app->group('', function (Group $group) {

        $group->post('/block-user',
            \App\controller\api\users\BlockUser::class
        );

        $group->post('/unblock-user',
            \App\controller\api\users\UnblockUser::class
        );

        $group->post('/users',
            \App\controller\api\users\FindUser::class
        );

        $group->group('/admin', function (Group $group) {

            $group->get('/users',
                \App\controller\admin\users\Users::class
            )->setName('users');

            $group->post('/manage-privileges',
                \App\controller\admin\users\ManagePrivilege::class
            );

        })
            ->add(\App\middleware\ActivePage::class);

    })->add(\App\middleware\access\AdminUsers::class)
        ->add(\App\middleware\role\AdminAuth::class)
        ->add(\App\middleware\Auth::class);

};