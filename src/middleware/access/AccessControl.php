<?php

namespace App\middleware\access;

use App\lib\Login;
use App\model\PrivilegesModel;
use App\service\UserService;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Slim\Psr7\Response;

abstract  class AccessControl{

    protected  PrivilegesModel $privileges;

    public function __construct(UserService $userService)
    {
        $this->privileges = $userService->findById(Login::getLogin())->getPrivileges();
    }

    abstract function hasAccess(): bool;

    public function __invoke(Request $request, RequestHandler $handler): ResponseInterface {

        if ($this->hasAccess()) {
            return $handler->handle($request);
        } else {
            $response = new Response();
            return $response->withHeader('Location', '/denied')->withStatus(302);
        }
    }
}
