<?php

/**
 * Boostrap php represent the dependency container for Donctrine
 * It is injecto into the routes so that entity manager can be access
 * globally.
 */

use App\model\enum\AnnouncementStatus;
use App\model\enum\IssuesStatus;
use App\model\enum\UserRole;
use App\service\AnnouncementService;
use App\service\DuesService;
use App\service\IssuesService;
use App\service\LoginHistoryService;
use App\service\LogsService;
use App\service\PaymentService;
use App\service\PriviligesService;
use App\service\ReceiptService;
use App\service\Service;
use App\service\SystemSettingService;
use App\service\TransactionLogsService;
use App\service\TransactionService;
use App\service\UserService;
use Doctrine\Common\Cache\Psr6\DoctrineProvider;
use Doctrine\DBAL\Types\Type;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Tools\Setup;
use Slim\Flash\Messages;
use Symfony\Component\Cache\Adapter\ArrayAdapter;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use UMA\DIC\Container;

// setup container
$container = new Container(require __DIR__ . '/settings.php');

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

$container->set('DB_CONFIG', static function (Container $c): array {
    /** @var array $settings */
    $settings = $c->get('settings');

    return  $settings['db'];
});

$container->set('DEFAULT_CREDENTIAL', static function (Container $c): array {
    /** @var array $settings */
    $settings = $c->get('settings');

    return  $settings['DEFAULT_CREDENTIAL'];
});


// Add the services to the container. 
$container->set(UserService::class, static function (Container $c) {
    return new UserService($c->get(EntityManager::class));
});

$container->set(PriviligesService::class, static function (Container $c) {
    return new PriviligesService($c->get(EntityManager::class));
});

$container->set(SystemSettingService::class, static function (Container $c) {
    return new SystemSettingService($c->get(EntityManager::class));
});


$container->set(DuesService::class, static function (Container $c) {
    return new DuesService($c->get(EntityManager::class));
});

$container->set(ReceiptService::class, static function (Container $c) {
    return new ReceiptService($c->get(EntityManager::class));
});

$container->set(LogsService::class, static function (Container $c) {
    return new LogsService($c->get(EntityManager::class));
});

$container->set(Messages::class, function (Container $container) {
    return new Messages();
});

$container->set(LoginHistoryService::class, function (Container $c) {
    return new LoginHistoryService($c->get(EntityManager::class));
});


// Add the services to the container. 
$container->set(TransactionService::class, static function (Container $c) {
    return new TransactionService($c->get(EntityManager::class));
});

// Add the services to the container. 
$container->set(AnnouncementService::class, static function (Container $c) {
    return new AnnouncementService($c->get(EntityManager::class));
});

// Add the services to the container. 
$container->set(PaymentService::class, static function (Container $c) {
    return new PaymentService($c->get(EntityManager::class));
});

// Add the services to the container. 
$container->set(TransactionLogsService::class, static function (Container $c) {
    return new TransactionLogsService($c->get(EntityManager::class));
});

$container->set(Service::class, static function (Container $c) {
    return new Service($c->get(EntityManager::class));
});

$container->set(IssuesService::class, static function (Container $c) {
    return new IssuesService($c->get(EntityManager::class));
});

$container->set(\App\service\CodeModelService::class, static function (Container $c) {
    return new \App\service\CodeModelService($c->get(EntityManager::class));
});

$container->set(\App\service\UserLogsService::class, static function (Container $c) {
    return new \App\service\UserLogsService($c->get(EntityManager::class));
});

$conn = $container->get(EntityManager::class)->getConnection();
$conn->getDatabasePlatform()->registerDoctrineTypeMapping("enum", "string");


try {
    Type::addType(AnnouncementStatus::class, AnnouncementStatus::class);
    Type::addType(IssuesStatus::class, IssuesStatus::class);
    Type::addType(UserRole::class, UserRole::class);
    Type::addType(\App\model\enum\BudgetStatus::class, \App\model\enum\BudgetStatus::class);
} catch (\Doctrine\DBAL\Exception $e) {
    echo $e->getMessage();
}

return $container;