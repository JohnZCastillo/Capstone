<?php

namespace App\controller\auth;

use App\controller\admin\AdminAction;
use App\controller\user\issues\PostIssue;
use App\exception\code\InvalidCode;
use App\lib\Mail;
use App\lib\Redirector;
use App\lib\Time;
use Exception;
use Psr\Http\Message\ResponseInterface as Response;

class VerifyUser extends AdminAction
{

    /**
     * @inheritDoc
     */
    protected function action(): Response
    {
        try {

            $user = $this->getLoginUser();

            if($user->isVerified()){
                return $this->redirect(Redirector::redirectToHome($user->getPrivileges()));
            }

            $formData = $this->getFormData();

            $code = implode($formData['code']);

            $valid = $this->codeModelService->isValid(session_id() , $code, new \DateTime());

            $user->setVerified(true);

            $this->userService->save($user);

            return $this->redirect(Redirector::redirectToHome($user->getPrivileges()));

        } catch (InvalidCode $invalidCode) {
            $this->addMessage('verify', $invalidCode->getMessage());
        }catch (Exception $e) {
            $this->addMessage('verify', 'Something went wrong');
        }

        return $this->redirect('/verify');

    }
}