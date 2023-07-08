<?php

namespace App\controller;

use Slim\Views\Twig;

class UserController {


    public function home($request, $response, $args) {
        $view = Twig::fromRequest($request);
        return $view->render($response, 'pages/user-home.html', []);
    }
}
