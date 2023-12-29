<?php

global $twig;

use Slim\App;
use Slim\Interfaces\RouteCollectorProxyInterface as Group;

return function (App $app) use ($twig) {

    $app->group('/admin', function (Group $group) {

        $group->get('/overview',
            \App\controller\admin\overview\ViewOverview::class
        )->setName('overview');

        $group->post('/overview',
            \App\controller\admin\overview\UpdateOverview::class
        );

        $group->post('/add-staff',
            \App\controller\admin\overview\AddStaff::class
        );

        $group->post('/remove-staff',
            \App\controller\admin\overview\RemoveStaff::class
        );

        $group->post('/add-feature',
            \App\controller\admin\overview\AddFeature::class
        );

        $group->post('/remove-feature',
            \App\controller\admin\overview\RemoveFeature::class
        );
    })->add(\App\middleware\role\SuperAdminAuth::class)
        ->add(\App\middleware\Auth::class)
        ->add(\App\middleware\ActivePage::class);

};