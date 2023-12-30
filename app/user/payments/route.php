<?php

global $twig;

use Slim\App;
use Slim\Interfaces\RouteCollectorProxyInterface as Group;

return function (App $app) use ($twig) {

    $app->group('', function (Group $group) {

        $group->get('/home',
            \App\controller\user\payments\ViewHomepage::class
        )->setName('home');


    })->add(\App\middleware\ActivePage::class)
        ->add(\App\middleware\role\UserAuth::class)
        ->add(\App\middleware\Auth::class);

};