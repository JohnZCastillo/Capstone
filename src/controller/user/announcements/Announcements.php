<?php

namespace App\controller\user\announcements;

use App\controller\user\UserAction;
use Psr\Http\Message\ResponseInterface as Response;

class Announcements extends UserAction
{

    /**
     * @inheritDoc
     */
    protected function action(): Response
    {

        $max = 10;

        $announcements = $this->announcementService->findAll();

        return $this->view('user/pages/announcements.html', [
            'announcements' => $announcements,
        ]);
    }
}