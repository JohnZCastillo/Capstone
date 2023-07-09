<?php

namespace App\controller;

use App\model\UserModel;
use App\service\UserService;
use Exception;
use Slim\Views\Twig;
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

        $view = Twig::fromRequest($request);

        // Creat user model
        $user = new UserModel();

        // update user information from post request parameters
        $user->setName($request->getParsedBody()['name']);
        $user->setEmail($request->getParsedBody()['email']);
        $user->setPassword($request->getParsedBody()['password']);
        $user->setBlock($request->getParsedBody()['block']);
        $user->setLot($request->getParsedBody()['lot']);

        try {
            $this->userSerivce->save($user);
            return $view->render($response, 'pages/user-home.html', []);
        } catch (Exception $e) {

            $data = [];

            //error code for duplicate entry
            if ($e->getCode() == 1062) {
                $data['message'] = "Email Is Already In Used";
            } else {
                $data['message'] = "Something Went Wrong";
            }

            $response->withStatus(500);
            return $view->render($response, 'pages/register.html', $data);
        }
    }
}
