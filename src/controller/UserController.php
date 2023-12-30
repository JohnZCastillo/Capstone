<?php

namespace App\controller;

use App\lib\Currency;
use App\lib\Filter;
use App\lib\GCashReceiptValidator;
use App\lib\Image;
use App\lib\Login;
use App\lib\ReferenceExtractor;
use App\lib\Time;
use App\model\enum\IssuesStatus;
use App\model\IssuesModel;
use App\model\LogsModel;
use App\model\TransactionModel;
use DateTime;
use Slim\Views\Twig;
use TCPDF;

class UserController extends Controller
{

    public function accountSettings($request, $response, $args)
    {

        $user = $this->getLogin();
        $name = $user->getName();
        $email = $user->getEmail();
        $block = $user->getBlock();
        $lot = $user->getLot();

        $loginHistory = $this->loginHistoryService->getLogs($user);
        $currentSession = session_id();

        $view = Twig::fromRequest($request);

        return $view->render($response, 'user/pages/account.html', [
            "loginHistory" => $loginHistory,
            "sessionId" => $currentSession,
            "name" => $name,
            "email" => $email,
            "block" => $block,
            "lot" => $lot,
            "user" => $user,
            "logs" => $user->getMyLogs(),
        ]);
    }

}