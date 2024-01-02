<?php

namespace App\controller\auth;

use App\controller\admin\AdminAction;
use App\exception\code\InvalidCode;
use App\exception\UserNotFoundException;
use App\lib\Mail;
use App\lib\Randomizer;
use App\model\UserLogsModel;
use Exception;
use Psr\Http\Message\ResponseInterface as Response;
use Respect\Validation\Validator as v;

class GenerateCode extends AdminAction
{

    /**
     * @inheritDoc
     */
    protected function action(): Response
    {

        $data = [];

        try {

            $content = $this->getFormData();

            $session = session_id();
            $code = $content['code'];
            $currentTime = new \DateTime();

            $this->codeModelService->isValid($session, $code, $currentTime);

            $user = $this->userService->findByEmail($content['email']);

            $user->setPassword(Randomizer::generateRandomPassword());
            $this->userService->save($user);

            $userName = $user->getName();
            $settings = $this->systemSettingService->findById();
            $tempPass = $user->getPassword();

            $emailBody = "
            Hello $userName,
            
            We have received a request to reset your password. Your temporary password : $tempPass.
            
            If you did not request a password reset, please secure your account.";

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

            $userLog = new UserLogsModel();

            $userLog->setUser($user);
            $userLog->setAction("Password was changed using forgot password", $user);
            $userLog->setCreatedAt(new \DateTime());

            $this->userLogsService->addLog($userLog);

            return $this->respondWithData([
                'message' => "A Temporary Password Was Sent To your Email",
            ]);

        } catch (UserNotFoundException $userNotFoundException) {
            $data['message'] = $userNotFoundException->getMessage();
        } catch (InvalidCode $invalidCode) {
            $data['message'] = $invalidCode->getMessage();
        } catch (Exception $e) {
            return $this->respondWithData(['message' => "Invalid Code"], 500);
        }

        return $this->respondWithData($data, 400);

    }

}

