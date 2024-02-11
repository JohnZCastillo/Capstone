<?php

namespace App\controller\admin\announcement;

use App\controller\admin\AdminAction;
use App\exception\announcement\AnnouncementNotFound;
use App\lib\Time;
use App\model\AnnouncementModel;
use App\model\enum\AnnouncementStatus;
use Exception;
use Psr\Http\Message\ResponseInterface as Response;

class CreateAnnouncement extends AdminAction
{

    protected function action(): Response
    {

        try {

            return $this->view('admin/pages/announcement.html', [
                'announcement' => null,
            ]);

        } catch (AnnouncementNotFound $announcementNotFound) {
            $this->addErrorMessage($announcementNotFound->getMessage());
        } catch (Exception $exception) {
            $this->addErrorMessage('An Internal Error Occurred');
        }
        return $this->redirect('/admin/announcements');

    }
}