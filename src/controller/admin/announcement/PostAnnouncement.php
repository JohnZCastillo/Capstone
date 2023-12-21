<?php

namespace App\controller\admin\announcement;

use App\controller\admin\AdminAction;
use App\exception\announcement\AnnouncementNotFound;
use App\exception\InvalidInput;
use App\model\AnnouncementModel;
use App\model\enum\AnnouncementStatus;
use Exception;
use Psr\Http\Message\ResponseInterface as Response;
use Respect\Validation\Validator as v;

class PostAnnouncement extends AdminAction
{

    protected function action(): Response
    {

        $id = $this->args['id'];

        try {

            $announcement = $this->announcementService->findById($id);

            $announcement->setStatus(AnnouncementStatus::posted());

            $this->announcementService->save($announcement);


        } catch (AnnouncementNotFound $announcementNotFound) {
            $this->addErrorMessage($announcementNotFound->getMessage());
        } catch (InvalidInput $invalidInput) {
            $this->addErrorMessage($invalidInput->getMessage());
        } catch (Exception $exception) {
            $this->addErrorMessage('An Internal Error Occurred');
        }

        return $this->redirect('/admin/announcements?status=ARCHIVED');
    }
}