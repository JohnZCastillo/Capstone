<?php

namespace App\middleware;

use App\lib\Login;
use App\model\StaffModel;
use App\service\AccessService;
use App\service\CategoryService;
use App\service\RoleService;
use App\service\UserService;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Slim\Psr7\Response;
use Slim\Routing\RouteContext;
use Slim\Views\Twig;
use UMA\DIC\Container;

class ActivePage
{

    public function __invoke(Request $request, RequestHandler $handler): ResponseInterface
    {

        $routeContext = RouteContext::fromRequest($request);
        $route = $routeContext->getRoute();

        if ($route === null) {
            return $handler->handle($request);
        }

        $twig = Twig::fromRequest($request);

        $twig->getEnvironment()->addGlobal('active_route', $route->getName());
        $twig->getEnvironment()->addGlobal('active_route_id', $route->getArgument('id'));

        return $handler->handle($request);
    }
}
