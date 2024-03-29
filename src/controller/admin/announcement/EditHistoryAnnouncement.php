<?php

namespace App\controller\admin\announcement;

use App\controller\admin\AdminAction;
use App\exception\announcement\AnnouncementNotFound;
use Exception;
use Psr\Http\Message\ResponseInterface as Response;

class EditHistoryAnnouncement extends AdminAction
{

    protected function action(): Response
    {

        $id = $this->args['id'];

        try {

            $history = $this->announcementHistoryService->findById($id);

            $announcement = $history->getAnnouncement();
            $announcement->setTitle($history->getTitle());
            $announcement->setContent($history->getContent());

            $mainId = $announcement->getId();

            return $this->view('admin/pages/announcement.html', [
                'announcement' => $announcement,
                'historyId' => $id,
                'mainId' => $mainId,
            ]);

        } catch (AnnouncementNotFound $announcementNotFound) {
            $this->addErrorMessage($announcementNotFound->getMessage());
        } catch (Exception $exception) {
            $this->addErrorMessage('An Internal Error Occurred');
        }

        return $this->redirect('/admin/announcements');
    }
}