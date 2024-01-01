<?php

namespace App\controller\user\account;

use App\controller\admin\AdminAction;
use Psr\Http\Message\ResponseInterface as Response;

class Account extends AdminAction
{

    /**
     * @inheritDoc
     */
    protected function action(): Response
    {

        try {

            $user = $this->getLoginUser();
            $name = $user->getName();
            $email = $user->getEmail();
            $block = $user->getBlock();
            $lot = $user->getLot();

            $loginHistory = $this->loginHistoryService->getLogs($user);
            $currentSession = session_id();

            return $this->view('user/pages/account.html', [
                "loginHistory" => $loginHistory,
                "sessionId" => $currentSession,
                "name" => $name,
                "email" => $email,
                "block" => $block,
                "lot" => $lot,
                "user" => $user,
                "logs" => $user->getMyLogs(),
            ]);

        } catch (\Exception $exception) {
            $this->addErrorMessage('Internal Error Occurred');
        }

        return $this->redirect('/home');
    }
}