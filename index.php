<?php

use App\controller\UserController;
use App\middleware\Auth;
use App\service\Service;
use App\service\UserService;
use Slim\Factory\AppFactory;
use Slim\Views\Twig;
use Slim\Views\TwigMiddleware;

use Doctrine\Common\Cache\Psr6\DoctrineProvider;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Tools\Setup;
use Symfony\Component\Cache\Adapter\ArrayAdapter;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
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
$app->post('/register', [UserController::class, 'register']);

// Return Signup View
$app->get('/register', function ($request, $response, $args) {
    $view = Twig::fromRequest($request);
    return $view->render($response, 'pages/register.html');
});


$app->run();
