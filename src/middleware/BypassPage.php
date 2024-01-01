<?php

namespace App\middleware;

use App\lib\Login;
use App\lib\Redirector;
use App\model\PrivilegesModel;
use App\model\UserModel;
use App\service\UserService;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Slim\Psr7\Response;
use UMA\DIC\Container;

class BypassPage
{

    protected UserService $userService;

    /**
     * @param UserService $userService
     */
    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
    }

    public function __invoke(Request $request, RequestHandler $handler): ResponseInterface
    {

        if (Login::isLogin()) {
            $location = Redirector::redirectToHome($this->userService->findById(Login::getLogin())->getPrivileges());
            $response = new Response();
            return $response->withHeader('Location', $location)->withStatus(302);
        }

        return $handler->handle($request);

    }
}