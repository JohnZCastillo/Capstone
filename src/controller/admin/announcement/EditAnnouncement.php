<?php

namespace App\controller\admin\announcement;

use App\controller\admin\AdminAction;
use App\exception\announcement\AnnouncementNotFound;
use Exception;
use Psr\Http\Message\ResponseInterface as Response;

class EditAnnouncement extends AdminAction
{

    protected function action(): Response
    {

        $id = $this->args['id'];
        $announcement = null;

        try {

            $announcement = $this->announcementService->findById($id);
        } catch (AnnouncementNotFound $announcementNotFound) {
            $this->addErrorMessage($announcementNotFound->getMessage());
            return $this->redirect('/admin/announcements');
        } catch (Exception $exception) {
            $this->addErrorMessage('An Internal Error Occurred');
        }

        return $this->view('admin/pages/announcement.html', [
            'announcement' => $announcement,
        ]);

    }
}