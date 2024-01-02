<?php

namespace App\controller\auth;

use App\controller\admin\AdminAction;
use App\exception\code\InvalidCode;
use App\exception\UserNotFoundException;
use App\Lib\Mail;
use App\lib\Randomizer;
use App\lib\Time;
use App\model\UserLogsModel;
use Exception;
use Psr\Http\Message\ResponseInterface as Response;
use Respect\Validation\Validator as v;

class NewCode extends AdminAction
{

    /**
     * @inheritDoc
     */
    protected function action(): Response
    {

        $data = [];

        try {

            $content = $this->getFormData();
            session_regenerate_id();

            $user = $this->userService->findByEmail($content['email']);

            $codeModel = $this->codeModelService->createCode(Time::createFutureTime(5));

            $settings = $this->systemSettingService->findById();

            $userName = $user->getName();
            $code = $codeModel->getCode();

            $emailBody = "
            Hello $userName,
            
            We have received a request to reset your password. Your verification code is: $code.
            
            If you did not request a password reset, please disregard this message.";

            $mailContent = [
                "senderEmail" => $settings->getMailUsername(),
                "senderName" => "Carrisa Homes Portal",
                "recieverEmail" => $user->getEmail(),
                "recieverName" => $user->getName(),
                "emailBody" => $emailBody,
                "emailSubject" => "Reset Password"
            ];

            Mail::setConfig($settings);
            $sent = Mail::send($mailContent);

            if (!$sent) {
                throw new Exception("Code was not sent");
            }

            return $this->respondWithData([
                'message' => "Code Sent",
            ]);

        } catch (UserNotFoundException $userNotFoundException) {
            $data['message'] = $userNotFoundException->getMessage();
        } catch (Exception $e) {

            var_dump($e->getMessage());
//            return $this->respondWithData(['message' => "Internal Error Occurred"], 500);
//            return $this->respondWithData(['message' => $e->getMessage()], 500);
        }
//        return $this->respondWithData($data, 400);
    }

}

