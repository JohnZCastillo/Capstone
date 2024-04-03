<?php

global $twig;

use Slim\App;
use Slim\Interfaces\RouteCollectorProxyInterface as Group;

return function (App $app) use ($twig) {

    $app->group('/admin', function (Group $group) {

        $group->get('/project', \App\controller\admin\project\Project::class)
            ->setName('project');

        $group->post('/new-project', \App\controller\admin\project\NewProject::class)
            ->setName('project');
    })
        ->add(\App\middleware\role\SuperAdminAuth::class)
        ->add(\App\middleware\Auth::class)
        ->add(\App\middleware\ActivePage::class);
};