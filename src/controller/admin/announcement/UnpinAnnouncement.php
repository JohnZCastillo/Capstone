<?php

namespace App\controller\admin\announcement;

use App\controller\admin\AdminAction;
use App\exception\announcement\AnnouncementNotFound;
use App\model\enum\LogsTag;
use Exception;
use Psr\Http\Message\ResponseInterface as Response;

class UnpinAnnouncement extends AdminAction
{

    protected function action(): Response
    {

        $id = $this->args['id'];

        try {

            $announcement = $this->announcementService->findById($id);

            $announcement->setPin(false);
            $announcement->setPinDate(null);

            $this->addActionLog("Announcement with $id was unpin",LogsTag::announcement());

            $this->announcementService->save($announcement);

        } catch (AnnouncementNotFound $announcementNotFound) {
            $this->addErrorMessage($announcementNotFound->getMessage());
        } catch (Exception $exception) {
            $this->addErrorMessage('An Internal Error Occurred');
        }

        return $this->redirect('/admin/announcements');
    }
}