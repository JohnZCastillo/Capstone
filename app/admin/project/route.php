<?php

global $twig;

use Slim\App;
use Slim\Interfaces\RouteCollectorProxyInterface as Group;

return function (App $app) use ($twig) {

    $app->group('/project', function (Group $group) {

        $group->get('/', \App\controller\admin\payments\Homepage::class)
            ->setName('home');

    })
        ->add(\App\middleware\role\SuperAdminAuth::class)
        ->add(\App\middleware\Auth::class)
        ->add(\App\middleware\ActivePage::class);
};