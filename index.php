<?php

use App\controller\UserController;
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

// setup container
$container = new Container(require __DIR__ . '/settings.php');

// Note for Dependency Injection

// setup entity
$container->set(EntityManager::class, static function (Container $c): EntityManager {
    /** @var array $settings */
    $settings = $c->get('settings');

    // Use the ArrayAdapter or the FilesystemAdapter depending on the value of the 'dev_mode' setting
    // You can substitute the FilesystemAdapter for any other cache you prefer from the symfony/cache library
    $cache = $settings['doctrine']['dev_mode'] ?
        DoctrineProvider::wrap(new ArrayAdapter()) :
        DoctrineProvider::wrap(new FilesystemAdapter(directory: $settings['doctrine']['cache_dir']));

    $config = Setup::createAttributeMetadataConfiguration(
        $settings['doctrine']['metadata_dirs'],
        $settings['doctrine']['dev_mode'],
        null,
        $cache
    );

    return EntityManager::create($settings['doctrine']['connection'], $config);
});

// setup user service
$container->set(UserService::class, static function (Container $c) {
    return new UserService($c->get(EntityManager::class));
});

$container->set(Service::class, static function (Container $c) {
    return new Service($c->get(EntityManager::class));
});


AppFactory::setContainer($container);

$app = AppFactory::create();

// Configure Twig view renderer
$twig = Twig::create('./src/views/', ['cache' => false]);

$app->add(TwigMiddleware::create($app, $twig));


$app->get('/', [UserController::class, 'home']);

$app->run();
