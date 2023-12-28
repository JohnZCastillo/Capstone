<?php

require __DIR__ . '/../vendor/autoload.php';

use DI\ContainerBuilder;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Tools\Console\ConsoleRunner;
use Doctrine\ORM\Tools\Console\EntityManagerProvider\SingleManagerProvider;
use Slim\Factory\AppFactory;

// Instantiate PHP-DI ContainerBuilder
$containerBuilder = new ContainerBuilder();

// Set up settings
$settings = require __DIR__ . '/../dependencies/container.php';
$settings($containerBuilder);

// Build PHP-DI Container instance
$container = $containerBuilder->build();

AppFactory::setContainer($container);
$app = AppFactory::create();

$commands = [];

ConsoleRunner::run(new SingleManagerProvider($app->getContainer()->get(EntityManager::class)), $commands);

