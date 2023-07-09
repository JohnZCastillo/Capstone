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
        //get the userService from dependency container
        $this->userSerivce = $container->get(UserService::class);
    }

    public function home($request, $response, $args) {
    }


    /**
     * Register new User to database.
     */
    public function register($request, $response, $args) {

        $user = new UserModel();

        $user->setName($request->getParsedBody()['name']);
        $user->setEmail($request->getParsedBody()['email']);
        $user->setPassword($request->getParsedBody()['password']);
        $user->setBlock($request->getParsedBody()['block']);
        $user->setLot($request->getParsedBody()['lot']);

        $this->userSerivce->save($user);
        
        $view = Twig::fromRequest($request);
        return $view->render($response, 'pages/user-home.html', []);
    }
}
