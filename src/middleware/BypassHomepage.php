<?php

namespace App\middleware;

use App\lib\Login;
use App\model\UserModel;
use App\service\UserService;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Slim\Flash\Messages;
use Slim\Psr7\Response;
use UMA\DIC\Container;

class BypassHomepage{

    public function __invoke(Request $request, RequestHandler $handler): ResponseInterface {

        if (!Login::isLogin()) {
            return $handler->handle($request);
        } else {
            $response = new Response();
            return $response->withHeader('Location', '/home')->withStatus(302);
        }
    }
}