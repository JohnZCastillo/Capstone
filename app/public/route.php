<?php

global $twig;

use Slim\App;

return function (App $app) use ($twig) {

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

    $app->post('/register',
        \App\controller\auth\RegisterAuth::class
    );

    $app->get('/forgot-password',
        \App\controller\auth\ViewForgotPassword::class
    );

    $app->post('/generate-code',
        \App\controller\auth\GenerateCode::class
    );

    $app->post('/new-code',
        \App\controller\auth\NewCode::class
    );

    $app->get('/terms-and-conditions',
        \App\controller\auth\ViewTermsAndCondition::class
    );
};