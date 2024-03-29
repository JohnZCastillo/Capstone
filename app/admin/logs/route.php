<?php

global $twig;

use Slim\App;
use Slim\Interfaces\RouteCollectorProxyInterface as Group;

return function (App $app) use ($twig) {

    $app->group('/admin', function (Group $group) {

        $group->get('/logs',
            \App\controller\admin\logs\Logs::class
        )->setName('logs');

    })->add(\App\middleware\role\SuperAdminAuth::class)
        ->add(\App\middleware\Auth::class)
        ->add(\App\middleware\ActivePage::class);
};