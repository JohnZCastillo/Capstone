<?php

namespace App\middleware;

use App\lib\Login;
use App\service\LoginHistoryService;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Slim\Flash\Messages;
use Slim\Psr7\Response;
use UMA\DIC\Container;

class ForceLogout
{

    private bool $forceLogout;

    public function __construct(LoginHistoryService $loginHistoryService)
    {
        $this->forceLogout = $loginHistoryService->isSessionActive(session_id());
    }

    public function __invoke(Request $request, RequestHandler $handler): ResponseInterface
    {

        if ($this->forceLogout) {
            return $handler->handle($request);
        } else {
            Login::forceLogout();

            $response = new Response();
            return $response->withHeader('Location', '/invalid-session')->withStatus(302);
        }
    }
}