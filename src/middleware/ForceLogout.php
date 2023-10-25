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
    private Messages $flashMessge;

    public function __construct(Container $container)
    {
        $this->flashMessge = $container->get(Messages::class);

        $loginHistoryService = $container->get(LoginHistoryService::class);
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