<?php

use App\controller\AdminController;
use App\controller\AuthController;
use App\controller\UserController;
use App\middleware\Auth;
use Slim\Factory\AppFactory;
use Slim\Views\Twig;
use Slim\Views\TwigMiddleware;
use UMA\DIC\Container;

require './vendor/autoload.php';

/** @var Container $container */
$container = require_once __DIR__ . '/bootstrap.php';

AppFactory::setContainer($container);

$app = AppFactory::create();

// Configure Twig view renderer
$twig = Twig::create('./src/views/', ['cache' => false]);

$app->add(TwigMiddleware::create($app, $twig));

// Register User
$app->post('/register', [AuthController::class, 'register']);

$app->get('/home', [UserController::class, 'home']);
$app->get('/dues', [UserController::class, 'dues']);
$app->get('/transaction/{id}', [UserController::class, 'transaction']);

$app->get('/test', [UserController::class, 'test']);

$app->get('/admin', [AdminController::class, 'home']);
$app->get('/admin/transaction/{id}', [AdminController::class, 'transaction']);
$app->post('/admin/transaction/reject', [AdminController::class, 'rejectPayment']);
$app->post('/admin/transaction/approve', [AdminController::class, 'approvePayment']);
$app->post('/admin/payment-settings', [AdminController::class, 'paymentSettings']);

$app->post('/pay', [UserController::class, 'pay']);

$app->post('/login', [AuthController::class, 'login']);

// Return Signup View
$app->get('/register', function ($request, $response, $args) {
    $view = Twig::fromRequest($request);
    return $view->render($response, 'pages/register.html');
});

// Return Signup View
$app->get('/login', function ($request, $response, $args) {
    $view = Twig::fromRequest($request);
    return $view->render($response, 'pages/login.html');
});


$app->run();