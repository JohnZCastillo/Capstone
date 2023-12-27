<?php

namespace App\middleware\role;

use App\lib\Login;
use App\lib\Redirector;
use App\model\PrivilegesModel;
use App\model\UserModel;
use App\service\UserService;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Slim\Psr7\Response;

abstract class RoleBaseAuth
{

    protected string $role;
    private UserModel $userModel;

    public function __construct(UserService $userService)
    {
        $this->userModel= $userService->findById(Login::getLogin());
        $this->role= $this->userModel->getRole();
    }

    abstract function isAllowed(): bool;

    public function __invoke(Request $request, RequestHandler $handler): ResponseInterface {

        if ($this->isAllowed()) {
            return $handler->handle($request);
        } else {
            $response = new Response();
            return $response->withHeader('Location','/denied')->withStatus(302);
        }
    }
}