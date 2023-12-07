<?php

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Tools\Console\ConsoleRunner;
use UMA\DIC\Container;
use Doctrine\ORM\Tools\Console\EntityManagerProvider\SingleManagerProvider;

require __DIR__ . '/vendor/autoload.php';


/** @var Container $container */
$container = require_once __DIR__ . '/bootstrap.php';

$entityManager = $container->get(EntityManager::class);

$commands = [];

ConsoleRunner::run(
    new SingleManagerProvider($entityManager),
    $commands
);