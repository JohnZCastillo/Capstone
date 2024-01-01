<?php

namespace App\controller\admin\overview;

use App\controller\admin\AdminAction;
use App\exception\InvalidInput;
use App\lib\Image;
use App\model\overview\Staff;
use Psr\Http\Message\ResponseInterface as Response;
use Respect\Validation\Validator as v;

class RemoveStaff extends AdminAction
{

    /**
     * @inheritDoc
     */
    protected function action(): Response
    {

        try {

            $content = $this->getFormData();

            $staff = $this->overviewService->getStaffByName($content['staff']);

            $this->overviewService->deleteStaff($staff);

            $this->addSuccessMessage('Staff was Remove');

        } catch (\Exception $exception) {

            $message = 'An Internal Error Occurred';

            if ($exception->getCode() == 1451) {
                $message = 'Cannot Remove Staff, please remove lower staff first';
            }

            $this->addErrorMessage($message);
        }

        return $this->redirect('/admin/overview#staffSection');
    }
}