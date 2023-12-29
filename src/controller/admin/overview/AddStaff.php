<?php

namespace App\controller\admin\overview;

use App\controller\admin\AdminAction;
use App\exception\InvalidInput;
use App\lib\Image;
use App\model\overview\Staff;
use Psr\Http\Message\ResponseInterface as Response;
use Respect\Validation\Validator as v;

class AddStaff extends AdminAction
{

    /**
     * @inheritDoc
     */
    protected function action(): Response
    {

        try {

            $path = './resources/staff/';


            $image = $_FILES['image'];

            $content = $this->getFormData();

            $superior = $this->overviewService->getStaffById($content['superior']);

            $staff = new Staff();
            $staff->setName($content['name']);
            $staff->setPosition($content['position']);

            if (isset($superior)) {
                $staff->setSuperior($superior);
            }

            if ($image['error'] !== UPLOAD_ERR_NO_FILE) {
                $imageName = Image::store($path, $image);
                $staff->setImg(str_replace('.', '', $path) . $imageName);
            }

            $this->overviewService->saveStaff($staff);

        } catch (InvalidInput $invalidInput) {
            $this->addErrorMessage($invalidInput->getMessage());
        } catch (\Exception $exception) {

            $message = 'An Internal Error Occurred';

            if ($exception->getCode() == 1062) {
                $message = 'Name is already define';
            }

            $this->addErrorMessage($message);
        }

        return $this->redirect('/admin/overview#staffSection');
    }
}