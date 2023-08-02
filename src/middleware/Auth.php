<?php

namespace App\middleware;

use App\lib\Login;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Slim\Flash\Messages;
use Slim\Psr7\Response;
use UMA\DIC\Container;

class Auth{

    private  Messages $flashMessge;
    public function __construct(Container  $container)
    {
        $this->flashMessge =  $container->get(Messages::class);
    }
    public function __invoke(Request $request, RequestHandler $handler): ResponseInterface {

        if (Login::isLogin()) {
            $response = $handler->handle($request);
            return $response;
        } else {
            $this->flashMessge->addMessage('AuthFailedMessage','You Must login First');
            $response = new Response();
            return $response->withHeader('Location', '/login')->withStatus(302);
        }
    }
}