<?php

namespace App\controller\admin\announcement;

use App\controller\admin\AdminAction;
use App\exception\InvalidInput;
use App\model\AnnouncementHistoryModel;
use App\model\AnnouncementModel;
use App\model\enum\AnnouncementStatus;
use App\model\enum\LogsTag;
use Exception;
use Psr\Http\Message\ResponseInterface as Response;
use Respect\Validation\Validator as v;

class MakeAnnouncement extends AdminAction
{

    protected function action(): Response
    {

        $formData = $this->getFormData();

        try {

            $title = $formData['title'];
            $content = $formData['content'];

            if (!v::stringType()->notEmpty()->validate($title)) {
                throw new InvalidInput('Announcement Title cannot be empty');
            }

            if (!v::stringType()->notEmpty()->validate($content)) {
                throw new InvalidInput('Announcement Content cannot be empty');
            }

            $announcemnent = new AnnouncementModel();

            $id = $formData['id'];

            if (v::stringType()->notEmpty()->validate($id)) {
                $announcemnent = $this->announcementService->findById($id);

                $announcemnentHistory = new AnnouncementHistoryModel();
                $announcemnentHistory->setAnnouncement($announcemnent);
                $announcemnentHistory->setTitle($announcemnent->getTitle());
                $announcemnentHistory->setContent($announcemnent->getContent());
            }

            $announcemnent->setTitle($title);
            $announcemnent->setContent($content);
            $announcemnent->setUser($this->getLoginUser());
            $announcemnent->setCreatedAt(new \DateTime());
            $announcemnent->setStatus(AnnouncementStatus::posted());

            $this->addActionLog("Announcement with $id was created",LogsTag::announcement());

            $this->announcementService->save($announcemnent);

            if (isset($announcemnentHistory)) {
                $this->announcementHistoryService->save($announcemnentHistory);
            }


        } catch (InvalidInput $invalidInput) {
            $this->addErrorMessage($invalidInput->getMessage());
        } catch (Exception $exception) {
            $this->addErrorMessage('An Internal Error Occurred');
        }

        return $this->redirect('/admin/announcements');
    }
}