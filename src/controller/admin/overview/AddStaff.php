<?php

namespace App\controller\admin\overview;

use App\controller\admin\AdminAction;
use App\exception\InvalidFile;
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

            $content = $this->getFormData();

            $rule = v::alnum(' ')->notEmpty();

            if(!$rule->validate($content['name'])){
                throw new InvalidInput('Invalid Staff Name');
            }

            if(!$rule->validate($content['position'])){
                throw new InvalidInput('Invalid Staff Position');
            }

            $superior = $this->overviewService->getStaffById($content['superior']);

            $staff = new Staff();
            $staff->setName($content['name']);
            $staff->setPosition($content['position']);

            if (isset($superior)) {
                $staff->setSuperior($superior);
            }

            $staff->setImg('/resources/staff-placeholder.png');

            $this->overviewService->saveStaff($staff);

        }  catch (InvalidFile $invalidFile) {
            $this->addErrorMessage($invalidFile->getMessage());
        }catch (InvalidInput $invalidInput) {
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