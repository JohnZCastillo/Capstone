<?php

namespace App\controller\auth;

use App\controller\admin\AdminAction;
use App\exception\code\ExistingCode;
use App\exception\code\InvalidCode;
use App\lib\Mail;
use App\lib\Redirector;
use App\lib\Time;
use Exception;
use Psr\Http\Message\ResponseInterface as Response;

class ViewVerify extends AdminAction
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

            if ($this->codeModelService->hasExistingValidCode(session_id(), new \DateTime())) {
                throw new ExistingCode('Please Wait for 5 minutes to request for new code');
            }

            $codeModel = $this->codeModelService->createCode(Time::createFutureTime(5));

            $settings = $this->systemSettingService->findById();

            $userName = $user->getName();
            $code = $codeModel->getCode();

            $emailBody = "
            Hello $userName,
            
            To verify Your account please use this verification code is: $code.";

            $mailContent = [
                "senderEmail" => $settings->getMailUsername(),
                "senderName" => "Carrisa Homes Portal",
                "recieverEmail" => $user->getEmail(),
                "recieverName" => $user->getName(),
                "emailBody" => $emailBody,
                "emailSubject" => "Email Verification"
            ];

            Mail::setConfig($settings);
            $sent = Mail::send($mailContent);

            if (!$sent) {
                throw new InvalidCode("Code was not sent");
            }

        } catch (ExistingCode $existingCode) {
            $this->addMessage('verify', $existingCode->getMessage());
        }catch (InvalidCode $invalidCode) {
            $this->addMessage('verify', $invalidCode->getMessage());
        } catch (Exception $e) {
            $this->addMessage('verify', 'Something went wrong');
        }

        return $this->view('verify.html', []);

    }
}