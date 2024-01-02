<?php

namespace App\controller\pdf;

use App\controller\admin\AdminAction;
use App\exception\UserNotFoundException;
use App\lib\BudgetReportDocx;
use App\Lib\Mail;
use App\lib\Time;
use App\model\UserModel;
use Exception;
use NcJoes\OfficeConverter\OfficeConverter;
use Slim\Psr7\Response;
use TCPDF;
use thiagoalessio\TesseractOCR\Tests\Common\TestCase;

class DownloadPdf extends AdminAction
{

    protected function action(): Response
    {

        try {

            $user = new UserModel();
            $user->setEmail('johnzunigacastillo@gmail.com');
            $user->setName('John Castillo');

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
            return $this->respondWithData(['message' => $e->getMessage()], 500);
        }

        return $this->respondWithData($data, 400);

    }
}