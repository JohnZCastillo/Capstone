<?php

namespace App\middleware;

use App\lib\Login;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Slim\Psr7\Response;

class Auth{

    public function __invoke(Request $request, RequestHandler $handler): ResponseInterface {            
        
        if (Login::isLogin()) {
            $response = $handler->handle($request);
            return $response;
        } else {
            $response = new Response();
            return $response->withHeader('Location', '/login')->withStatus(302);
        }
    }
}