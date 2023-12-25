<?php

namespace App\controller\api\users;

use App\controller\admin\AdminAction;
use App\exception\NotAuthorizeException;
use App\exception\users\LoginHistoryNotFound;
use DateTime;
use Exception;
use Psr\Http\Message\ResponseInterface as Response;

class ForceLogout extends AdminAction
{

    /**
     * @inheritDoc
     */
    protected function action(): Response
    {

        $formData = $this->getFormData();

        try {

            $session = $formData['session'];

            $login = $this->loginHistoryService->getBySession($session);


            if ($login->getUser()->getId() != $this->getLoginUser()->getId()) {
                throw new NotAuthorizeException('Access Denied');
            }

            $login->setLogoutDate(new DateTime());
            $this->loginHistoryService->save($login);

            return $this->respondWithData(['logout' => $login->getLogoutDate()->format("M d, Y h:i:s a")]);
        } catch (NotAuthorizeException $notAuthorizeException) {
            return $this->respondWithData(['message' => $notAuthorizeException->getMessage()], 401);
        } catch (LoginHistoryNotFound $loginHistoryNotFound) {
            return $this->respondWithData(['message' => $loginHistoryNotFound->getMessage()], 404);
        } catch (Exception $e) {
            return $this->respondWithData(['message' => 'An internal error occurred'], 500);
        }
    }
}