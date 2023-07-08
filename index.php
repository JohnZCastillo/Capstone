<?php
use App\controller\UserController;
use Slim\Factory\AppFactory;
use Slim\Views\Twig;
use Slim\Views\TwigMiddleware;

require './vendor/autoload.php';

$app = AppFactory::create();

$twig = Twig::create('./src/views/', ['cache' => false]);

// Add Twig-View Middleware
$app->add(TwigMiddleware::create($app, $twig));

$app->get('/', [UserController::class ,'home']);

$app->run();