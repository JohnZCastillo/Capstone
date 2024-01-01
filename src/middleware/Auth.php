<?php

namespace App\middleware;

use App\exception\NotAuthorizeException;
use App\exception\users\UserBlockException;
use App\lib\Login;
use App\service\LoginHistoryService;
use App\service\UserService;
use Exception;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Slim\Flash\Messages;
use Slim\Psr7\Response;

class Auth
{
    private UserService $userService;
    protected  LoginHistoryService $loginHistoryService;

    protected Messages $messages;

    public function __construct(UserService $userService, LoginHistoryService $loginHistoryService, Messages $messages)
    {
        $this->userService = $userService;
        $this->loginHistoryService = $loginHistoryService;
        $this->messages = $messages;
    }

    public function __invoke(Request $request, RequestHandler $handler): ResponseInterface
    {

        try {

            if (!Login::isLogin()) {
                throw new NotAuthorizeException('You Must Login First');
            }

            //only allow if session is active; force logout inactive session
            if(!$this->loginHistoryService->isSessionActive(session_id())){
                throw new Exception('Invalid Session');
            }

            if ($this->userService->findById(Login::getLogin())->getIsBlocked()) {
                throw new UserBlockException('Access Denied');
            }

            return $handler->handle($request);

        } catch (NotAuthorizeException $notAuthorizeException) {
            $this->messages->addMessage('loginError',$notAuthorizeException->getMessage());
        } catch (UserBlockException $userBlockException) {
            $this->messages->addMessage('loginError',$userBlockException->getMessage());
        } catch (Exception $exception) {
            $this->messages->addMessage('loginError','Something Went Wrong');
        }

        Login::forceLogout('slimFlash');

        $response = new Response();
        return $response->withHeader('Location', '/login')->withStatus(302);
    }
}