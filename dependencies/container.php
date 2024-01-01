<?php

declare(strict_types=1);

use Doctrine\Common\Cache\Psr6\DoctrineProvider;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Tools\Setup;
use Symfony\Component\Cache\Adapter\ArrayAdapter;
use DI\ContainerBuilder;
use Psr\Container\ContainerInterface;

return function (ContainerBuilder $containerBuilder) {

    $settings = require __DIR__ . '/settings.php';
    $dependencies = require __DIR__ . '/dependencies.php';

    $containerBuilder->addDefinitions($settings);

    $containerBuilder->addDefinitions([
        EntityManager::class => function (ContainerInterface $container): EntityManager {

            /** @var array $settings */
            $doctrine = $container->get('doctrine');

            // Use the ArrayAdapter or the FilesystemAdapter depending on the value of the 'dev_mode' setting
            // You can substitute the FilesystemAdapter for any other cache you prefer from the symfony/cache library
            $cache = $doctrine['dev_mode'] ?
                DoctrineProvider::wrap(new ArrayAdapter()) :
                DoctrineProvider::wrap(new FilesystemAdapter(directory: $doctrine['cache_dir']));

            $config = Setup::createAttributeMetadataConfiguration(
                $doctrine['metadata_dirs'],
                $doctrine['dev_mode'],
                null,
                $cache
            );

            $entityManager = EntityManager::create($doctrine['connection'], $config);

            $conn = $entityManager->getConnection();

            $config->addCustomStringFunction('MONTH',
                \DoctrineExtensions\Query\Mysql\Month::class);
            $config->addCustomStringFunction('YEAR',
                \DoctrineExtensions\Query\Mysql\Year::class
            );

            $conn->getDatabasePlatform()->registerDoctrineTypeMapping("enum", "string");

            require __DIR__ . '/types.php';

            return $entityManager;
        },
    ]);

    $containerBuilder->addDefinitions($dependencies);

};
