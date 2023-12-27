<?php

global $twig;

use Slim\App;

return function (App $app) use($twig){

    $app->get('/login',
        \App\controller\auth\ViewLogin::class
    );

    $app->post('/login',
        \App\controller\auth\LoginAuth::class
    );

    $app->get('/logout',
        \App\controller\auth\LogoutAuth::class
    );

    $app->get('/register',
        \App\controller\auth\ViewRegister::class
    );
};