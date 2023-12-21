<?php

use DI\ContainerBuilder;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Tools\Console\ConsoleRunner;
use UMA\DIC\Container;
use Doctrine\ORM\Tools\Console\EntityManagerProvider\SingleManagerProvider;

require __DIR__ . '/vendor/autoload.php';


// Instantiate PHP-DI ContainerBuilder
$containerBuilder = new ContainerBuilder();

// Set up settings
$settings = require __DIR__ . '/dependencies/container.php';
$settings($containerBuilder);

// Build PHP-DI Container instance
$container = $containerBuilder->build();

$entityManager = $container->get(EntityManager::class);

$commands = [];

ConsoleRunner::run(
    new SingleManagerProvider($entityManager),
    $commands
);