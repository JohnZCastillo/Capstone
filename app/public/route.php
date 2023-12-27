<?php

global $twig;

use Slim\App;

return function (App $app) use ($twig) {

    $app->get('/login',
        \App\controller\auth\ViewLogin::class
    )->add(\App\middleware\BypassPage::class);

    $app->post('/login',
        \App\controller\auth\LoginAuth::class
    )->add(\App\middleware\BypassPage::class);

    $app->get('/logout',
        \App\controller\auth\LogoutAuth::class
    );

    $app->get('/register',
        \App\controller\auth\ViewRegister::class
    )->add(\App\middleware\BypassPage::class);

    $app->post('/register',
        \App\controller\auth\RegisterAuth::class
    )->add(\App\middleware\BypassPage::class);

    $app->get('/forgot-password',
        \App\controller\auth\ViewForgotPassword::class
    )->add(\App\middleware\BypassPage::class);

    $app->post('/generate-code',
        \App\controller\auth\GenerateCode::class
    )->add(\App\middleware\BypassPage::class);

    $app->post('/new-code',
        \App\controller\auth\NewCode::class
    )->add(\App\middleware\BypassPage::class);

    $app->get('/terms-and-conditions',
        \App\controller\auth\ViewTermsAndCondition::class
    );

    $app->get('/denied',
        \App\controller\auth\ViewDenied::class
    );

    $app->get('/signupNotAllowed',
        \App\controller\auth\ViewSignupNotAllowed::class
    );

    $app->get('/',
        \App\controller\auth\ViewLandingPage::class
    );

    $app->get('/pdf',
        \App\controller\pdf\DownloadPdf::class
    );
};