<?php

namespace App\middleware;

use App\lib\Login;
use App\service\UserService;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Slim\Flash\Messages;
use Slim\Psr7\Response;
use UMA\DIC\Container;

class Auth
{

    private Messages $flashMessge;
    private UserService $userService;

    public function __construct(Container $container)
    {
        $this->flashMessge = $container->get(Messages::class);
        $this->userService = $container->get(UserService::class);
    }

    public function __invoke(Request $request, RequestHandler $handler): ResponseInterface
    {

        $isLogin = Login::isLogin();

        if (!$isLogin) {
            $this->flashMessge->addMessage('AuthFailedMessage', 'You Must login First');
            $response = new Response();
            return $response->withHeader('Location', '/login')->withStatus(302);
        }

        $user = $this->userService->findById(Login::getLogin());
        $allowed = !$user->getIsBlocked();

        if (!$allowed) {
            Login::forceLogout();
            $response = new Response();
            return $response->withHeader('Location', '/blocked')->withStatus(302);
        }

        $response = $handler->handle($request);
        return $response;

    }
}