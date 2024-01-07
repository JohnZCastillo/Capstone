<?php

namespace App\middleware;

use App\exception\NotAuthorizeException;
use App\exception\users\UserBlockException;
use App\exception\users\UserNotVerifiedException;
use App\lib\Login;
use App\service\LoginHistoryService;
use App\service\UserService;
use Exception;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Slim\Flash\Messages;
use Slim\Psr7\Response;
use Slim\Routing\RouteContext;

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

        $location = '/login';

        try {

            if (!Login::isLogin()) {
                throw new NotAuthorizeException('You Must Login First');
            }

//            only allow if session is active; force logout inactive session
            if(!$this->loginHistoryService->isSessionActive(session_id())){
                throw new Exception('Invalid Session');
            }

            if ($this->userService->findById(Login::getLogin())->getIsBlocked()) {
                throw new UserBlockException('Access Denied');
            }

            if(!$this->userService->findById(Login::getLogin())->isVerified()){

                $routeContext = RouteContext::fromRequest($request);
                $route = $routeContext->getRoute();

                if($route->getPattern() !== '/verify'){
                    throw new UserNotVerifiedException('Please Verify your email');
                }
            }

            return $handler->handle($request);

        } catch (UserNotVerifiedException $userNotVerifiedException) {
            $this->messages->addMessage('loginError',$userNotVerifiedException->getMessage());
            $location = '/verify';
        }catch (NotAuthorizeException $notAuthorizeException) {
            Login::forceLogout('slimFlash');
            $this->messages->addMessage('loginError',$notAuthorizeException->getMessage());
        } catch (UserBlockException $userBlockException) {
            Login::forceLogout('slimFlash');
            $this->messages->addMessage('loginError',$userBlockException->getMessage());
        } catch (Exception $exception) {
            Login::forceLogout('slimFlash');
            $this->messages->addMessage('loginError',$exception->getMessage());
        }

        $response = new Response();
        return $response->withHeader('Location', $location)->withStatus(302);
    }
}