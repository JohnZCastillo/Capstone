<?php

global $twig;

use Slim\App;
use Slim\Interfaces\RouteCollectorProxyInterface as Group;

return function (App $app) use ($twig) {

    $app->group('', function (Group $group) {

        $group->post('/upload',
            \App\controller\api\upload\FileUpload::class
        );

        $group->group('/admin', function (Group $group) {

            $group->get('/announcements',
                \App\controller\admin\announcement\Announcements::class
            )->add(\App\middleware\access\AdminAnnouncements::class)->setName('announcements');

            $group->get('/announcement',
                \App\controller\admin\announcement\CreateAnnouncement::class
            )->setName('announcements');

            $group->get('/announcement/edit/{id}',
                \App\controller\admin\announcement\EditAnnouncement::class
            )->setName('announcements');

            $group->get('/announcement/edit/history/{id}',
                \App\controller\admin\announcement\EditHistoryAnnouncement::class
            )->setName('announcements');

            $group->post('/announcement/post',
                \App\controller\admin\announcement\MakeAnnouncement::class
            )->setName('announcements');

            $group->post('/announcement/archive/{id}',
                \App\controller\admin\announcement\ArchiveAnnouncement::class
            )->setName('announcements');

            $group->post('/announcement/post/{id}',
                \App\controller\admin\announcement\PostAnnouncement::class
            )->setName('announcements');

            $group->post('/announcement/pin/{id}',
                \App\controller\admin\announcement\PinAnnouncement::class
            )->setName('announcements');

            $group->post('/announcement/unpin/{id}',
                \App\controller\admin\announcement\UnpinAnnouncement::class
            )->setName('announcements');

        })
            ->add(\App\middleware\ActivePage::class);

    })->add(\App\middleware\access\AdminAnnouncements::class)
        ->add(\App\middleware\role\AdminAuth::class)
        ->add(\App\middleware\Auth::class);
};