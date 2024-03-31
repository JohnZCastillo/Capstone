<?php

use DI\Container;
use DI\ContainerBuilder;
use Slim\Factory\AppFactory;
use Slim\Views\Twig;
use Slim\Views\TwigMiddleware;
use Twig\Extra\Intl\IntlExtension;

session_start();

const APP_ROOT = __DIR__ . '/../';

require APP_ROOT . 'vendor/autoload.php';

date_default_timezone_set('Asia/Manila');

$container = new Container();

$containerBuilder = new ContainerBuilder();
$settings = require APP_ROOT . 'dependencies/container.php';
$settings($containerBuilder);

$container = $containerBuilder->build();

AppFactory::setContainer($container);

//$containerBuilder->enableCompilation(APP_ROOT . '/cache');

$app = AppFactory::create();

// Configure Twig view renderer
$twig = Twig::create(APP_ROOT . 'public/views/', ['cache' => false, 'debug' => true]);
$twig->addExtension(new \Twig\Extension\DebugExtension());
$twig->addExtension(new IntlExtension());
$twig->getEnvironment()->getExtension(\Twig\Extension\CoreExtension::class)->setTimezone('Asia/Manila');

$container->set('view',$twig);

$app->add(TwigMiddleware::create($app, $twig));

$flashMessage = $container->get(\Slim\Flash\Messages::class);
$areaService = $blockService = $container->get(\App\service\AreaService::class);

$twig->addExtension($app->getContainer()->get(\App\lib\LotFinder::class));
$twig->getEnvironment()->addGlobal('verify',$flashMessage->getFirstMessage('verify'));
$twig->getEnvironment()->addGlobal('loginError',$flashMessage->getFirstMessage('loginError'));
$twig->getEnvironment()->addGlobal('errorMessage',$flashMessage->getFirstMessage('errorMessage'));
$twig->getEnvironment()->addGlobal('successMessage',$flashMessage->getFirstMessage('successMessage'));
$twig->getEnvironment()->addGlobal('login_user',$container->get('LOGIN_USER'));
$twig->getEnvironment()->addGlobal('blocks',$areaService->getBlock());

// Register routes
$routes = require APP_ROOT . 'app/routes.php';
$routes($app);

//
//$errorMiddleware = $app->addErrorMiddleware(false, true, true);
//
//// Get the default error handler and register my custom error renderer.
//$errorHandler = $errorMiddleware->getDefaultErrorHandler();
//$errorHandler->registerErrorRenderer('text/html', \App\middleware\MissingPage::class);

$app->run();