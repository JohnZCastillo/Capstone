<?php

namespace App\middleware;

use App\lib\Login;
use App\model\UserModel;
use App\service\UserService;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Slim\Psr7\Response;
use UMA\DIC\Container;

class AdminPaymentAuth{

    private  UserModel $user;

    public function __construct(Container  $container)
    {
        $loginId = Login::getLogin();
        $userService = $container->get(UserService::class);
        $this->user= $userService->findById($loginId);
    }

    public function __invoke(Request $request, RequestHandler $handler): ResponseInterface {

        $isAllowed = $this->user->getPrivileges()->getAdminPayment();

        if ($isAllowed) {
            return $handler->handle($request);
        } else {
            $response = new Response();
            return $response->withHeader('Location', '/denied')->withStatus(302);
        }
    }
}
