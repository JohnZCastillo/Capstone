<?php

namespace App\controller\auth;

use App\controller\admin\AdminAction;
use Psr\Http\Message\ResponseInterface as Response;

class ViewTermsAndCondition extends AdminAction
{

    /**
     * @inheritDoc
     */
    protected function action(): Response
    {
        $data['termsAndCondition'] = $this->systemSettingService->findById()->getTermsAndCondition();

       return  $this->view('terms-and-condition.html', $data);
    }
}