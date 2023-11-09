<?php

namespace App\middleware;

use App\lib\Login;
use App\lib\Redirector;
use App\model\UserModel;
use App\service\UserService;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Slim\Psr7\Response;
use UMA\DIC\Container;

class BypassHomepage
{

    private UserModel $user;
    private Container $container;

    public function __construct(Container $container)
    {
        $this->container = $container;

    }

    public function __invoke(Request $request, RequestHandler $handler): ResponseInterface
    {

        if (!Login::isLogin()) {
            return $handler->handle($request);
        } else {
            $loginId = Login::getLogin();
            $userService = $this->container->get(UserService::class);
            $this->user = $userService->findById($loginId);
            $location = Redirector::redirectToHome($this->user->getPrivileges());
            $response = new Response();
            return $response->withHeader('Location', $location)->withStatus(302);
        }
    }
}