<?php

namespace App\controller\admin\system;

use App\controller\admin\AdminAction;
use Psr\Http\Message\ResponseInterface as Response;

class ViewSettings extends AdminAction
{

    /**
     * @inheritDoc
     */
    protected function action(): Response
    {

        $settings = null;

        try {
            $settings = $this->systemSettingService->findById();
        }catch (\Exception $exception){
            $this->addErrorMessage('Internal Error Occurred');
        }
        return  $this->view('admin/pages/system.html',[
            'systemSettings' => $settings,
        ]);

    }
}