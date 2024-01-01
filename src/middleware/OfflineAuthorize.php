<?php

namespace App\middleware;

use App\lib\Login;
use App\service\UserService;
use Doctrine\DBAL\Driver\PDO\Exception;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Slim\Psr7\Response;
use UMA\DIC\Container;

class OfflineAuthorize
{

    private Container $container;

    public function __construct(Container $container)
    {
        $this->container = $container;
    }

    public function __invoke(Request $request, RequestHandler $handler): ResponseInterface
    {

        try {

            if (Login::isLogin()) {
                $userService = $this->container->get(UserService::class);
                $user = $userService->findById(Login::getLogin());

                if ($user->getRole() !== 'super') {
                    throw new Exception('Not Authorize');
                }
            } else {

                $content = $request->getParsedBody();

                if (isset($content['offlineUsername'], $content['offlinePassword'])) {
                    $credential =  $this->container->get('DEFAULT_CREDENTIAL');
                    $matchUsername = $content['offlineUsername'] == $credential['username'];
                    $matchPassword = $content['offlinePassword'] == $credential['password'];
                    Login::setOfflineLogin($matchUsername && $matchPassword);

                }else{
                    if(!Login::isOfflineLogin()){
                        throw new \Exception('Please Login First!');
                    }
                }

            }

        } catch (\Exception $e) {

            $response = new Response();

            return $response
                ->withHeader('Location', "/offline-login")
                ->withStatus(302);
        }

        return $handler->handle($request);
    }

}