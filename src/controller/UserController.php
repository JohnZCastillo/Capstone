<?php

namespace App\controller;

use App\model\UserModel;
use App\service\UserService;
use Slim\Views\Twig;
use Psr\Container\ContainerInterface;
use UMA\DIC\Container;

class UserController {

    private UserService $userSerivce;

    public function __construct(Container  $container) {
        $this->userSerivce = $container->get(UserService::class);
    }
    
    public function home($request, $response, $args) {

        $user = new UserModel();
        $user->setName('hi');

        $this->userSerivce->save($user);

        $view = Twig::fromRequest($request);
        return $view->render($response, 'pages/user-home.html', []);
    }
}
