<?php

session_cache_limiter(false);
session_start();

use App\controller\AdminController;
use App\controller\ApiController;
use App\controller\AuthController;
use App\controller\UserController;
use App\middleware\Auth;
use Slim\Factory\AppFactory;
use Slim\Views\Twig;
use Slim\Views\TwigMiddleware;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use UMA\DIC\Container;

require './vendor/autoload.php';

/** @var Container $container */
$container = require_once __DIR__ . '/bootstrap.php';

AppFactory::setContainer($container);

$app = AppFactory::create();

// Configure Twig view renderer
$twig = Twig::create('./src/views/', ['cache' => false]);

$app->add(TwigMiddleware::create($app, $twig));

// Protected Routes
$app->group('', function ($app) {
    $app->get('/home', [UserController::class, 'home']);
    $app->get('/dues', [UserController::class, 'dues']);
    $app->get('/transaction/{id}', [UserController::class, 'transaction']);
    $app->post('/pay', [UserController::class, 'pay']);
    $app->get('/announcements', [UserController::class, 'announcements']);
})->add(new Auth());

$app->group('/admin', function ($app) {
    $app->get('/home', [AdminController::class, 'home']);
    $app->get('/transaction/{id}', [AdminController::class, 'transaction']);
    $app->post('/transaction/reject', [AdminController::class, 'rejectPayment']);
    $app->post('/transaction/approve', [AdminController::class, 'approvePayment']);
    $app->post('/payment-settings', [AdminController::class, 'paymentSettings']);

    $app->post('/announcement', [AdminController::class, 'announcement']);
    
    $app->get('/announcement/edit/{id}', [AdminController::class, 'editAnnouncement']);
    $app->get('/announcement/delete/{id}', [AdminController::class, 'deleteAnnouncement']);

    $app->get('/announcements', [AdminController::class, 'announcements']);
    
})->add(new Auth());

$app->post('/upload', [ApiController::class, 'upload']);

// Public Routes
$app->post('/register', [AuthController::class, 'register']);
$app->get('/test', [UserController::class, 'test']);
$app->post('/login', [AuthController::class, 'login']);

// Return Signup View
$app->get('/register', function (Request $request, Response $response) use ($twig) {
    return $twig->render($response, 'pages/register.html');
});

$app->get('/logout', function (Request $request, Response $response) {
    session_destroy();
    return $response;
});

// Return Login View
$app->get('/login', function (Request $request, Response $response) use ($twig) {
    return $twig->render($response, 'pages/login.html');
});

// Return Login View
$app->get('/admin/announcement', function (Request $request, Response $response) use ($twig) {
    return $twig->render($response, 'pages/admin-announcement.html');
});



$app->run();
