<?php

namespace App\controller\admin\system;

use App\controller\admin\AdminAction;
use App\model\enum\LogsTag;
use Psr\Http\Message\ResponseInterface as Response;

class UpdateSettings extends AdminAction
{

    /**
     * @inheritDoc
     */
    protected function action(): Response
    {

        try {
            $formData = $this->getFormData();

            $systemSettings = $this->systemSettingService->findById();

            $systemSettings->setTermsAndCondition($formData['termsAndCondition']);
            $systemSettings->setMailHost($formData['mailHost']);
            $systemSettings->setMailUsername($formData['mailUsername']);

            if (!empty($formData['mailPassword'])) {
                $systemSettings->setMailPassword($formData['mailPassword']);
            }

            if (isset($formData['allowSignup'])) {
                $systemSettings->setAllowSignup(true);
            } else {
                $systemSettings->setAllowSignup(false);
            }

            if (isset($formData['regex'])) {
                $systemSettings->setRegex($formData['regex']);
            }

            $this->addActionLog('Update System Settings',LogsTag::systemSettings());

            $this->systemSettingService->save($systemSettings);

            $this->addSuccessMessage('Settings Updated!');
        } catch (\Exception $exception) {
            $this->addErrorMessage('Internal Error Occurred');
        }
        return $this->redirect('/admin/system');
    }
}