<?php

namespace App\controller\admin\announcement;

use App\controller\admin\AdminAction;
use App\exception\announcement\AnnouncementNotFound;
use App\lib\Filter;
use App\model\AnnouncementModel;
use Exception;
use Psr\Http\Message\ResponseInterface as Response;
use Slim\Views\Twig;

class EditHistoryAnnouncement extends AdminAction
{

    protected function action(): Response
    {

        $id = $this->args['id'];
        $announcement = null;
        $mainId = null;

        try {

            $history = $this->announcementHistoryService->findById($id);

            $announcement = $history->getAnnouncement();
            $announcement->setTitle($history->getTitle());
            $announcement->setContent($history->getContent());

            $mainId = $announcement->getId();

        } catch (AnnouncementNotFound $announcementNotFound) {
            $this->addErrorMessage($announcementNotFound->getMessage());
            return $this->redirect('/admin/announcements');
        } catch (Exception $exception) {
            $this->addErrorMessage('An Internal Error Occurred');
        }

        return $this->view('admin/pages/announcement.html', [
            'announcement' => $announcement,
            'historyId' => $id,
            'mainId' => $mainId,
        ]);

    }
}