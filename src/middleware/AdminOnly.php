<?php

namespace App\middleware;

use App\exception\NotAuthorizeException;
use App\exception\UserNotFoundException;
use App\exception\users\UserBlockException;
use App\exception\users\UserNotVerifiedException;
use App\lib\Login;
use App\lib\Redirector;
use App\service\LoginHistoryService;
use App\service\UserService;
use DoctrineExtensions\Query\Mysql\Log;
use Exception;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Slim\Flash\Messages;
use Slim\Psr7\Response;
use Slim\Routing\RouteContext;

class AdminOnly
{
    private UserService $userService;
    private  Messages $messages;

    public function __construct(UserService $userService, Messages $messages)
    {
        $this->userService = $userService;
        $this->messages = $messages;
    }

    public function __invoke(Request $request, RequestHandler $handler): ResponseInterface
    {

        $user = $this->userService->findById(Login::getLogin());

        try {

            $routeContext = RouteContext::fromRequest($request);
            $route = $routeContext->getRoute();
            $id = $route->getArgument('id');

            $target = $this->userService->findById($id);

            if($user->getRole() === 'admin' && $target->getRole() !== 'user' ){
                throw new NotAuthorizeException('User not authorized');
            }

            return $handler->handle($request);

        } catch (NotAuthorizeException $notAuthorizeException) {
            $this->messages->addMessage('errorMessage',$notAuthorizeException->getMessage());
        } catch (UserNotFoundException $userNotFoundException) {
            $this->messages->addMessage('errorMessage',$user->getMessage());
        } catch (Exception $exception) {
            $this->messages->addMessage('errorMessage',$exception->getMessage());
        }

        $response = new Response();
        return $response->withHeader('Location', Redirector::redirectToHome($user->getPrivileges()))->withStatus(302);
    }
}