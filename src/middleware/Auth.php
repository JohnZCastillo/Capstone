<?php

namespace App\middleware;

use App\exception\NotAuthorizeException;
use App\exception\users\UserBlockException;
use App\lib\Login;
use App\service\UserService;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Slim\Flash\Messages;
use Slim\Psr7\Response;
use Slim\Views\Twig;
use UMA\DIC\Container;

class Auth
{
    private UserService $userService;

    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
    }

    public function __invoke(Request $request, RequestHandler $handler): ResponseInterface
    {

        $location = '/login';

        try {

            if (!Login::isLogin()) {
                throw new NotAuthorizeException('You Must Login First');
            }

            if ($this->userService->findById(Login::getLogin())->getIsBlocked()) {
                throw new UserBlockException('Access Denied');
            }

            return $handler->handle($request);

        } catch (NotAuthorizeException $notAuthorizeException) {
            Twig::fromRequest($request)->getEnvironment()->addGlobal('loginError', $notAuthorizeException->getMessage());
        } catch (UserBlockException $userBlockException) {
            Login::forceLogout();
            $location = '/blocked';
        } catch (\Exception $exception) {
            Twig::fromRequest($request)->getEnvironment()->addGlobal('loginError', 'An Internal Error Occurred');
        }

        $response = new Response();
        return $response->withHeader('Location', $location)->withStatus(302);
    }
}