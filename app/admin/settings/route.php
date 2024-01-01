<?php

global $twig;

use Slim\App;
use Slim\Interfaces\RouteCollectorProxyInterface as Group;

return function (App $app) use ($twig) {

    $app->group('/admin', function (Group $group) {

        $group->get('/system',
            \App\controller\admin\system\ViewSettings::class
        )->setName('system');

        $group->post('/system',
            \App\controller\admin\system\UpdateSettings::class
        )->setName('system');

    })->add(\App\middleware\role\SuperAdminAuth::class)
        ->add(\App\middleware\Auth::class)
        ->add(\App\middleware\ActivePage::class);

};