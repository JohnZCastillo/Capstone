<?php

use App\service\AnnouncementHistoryService;
use App\service\AnnouncementService;
use App\service\CodeModelService;
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
use Doctrine\ORM\EntityManager;
use Psr\Container\ContainerInterface;
use Slim\Flash\Messages;

//Add Dependencies Here
return array(

    'DB_CONFIG' => function (ContainerInterface $c): array {
        return $c->get('db');
    },

    'DEFAULT_CREDENTIAL' => function (ContainerInterface $c): array {
        return $c->get('DEFAULT_CREDENTIAL');
    },

    UserService::class => function (ContainerInterface $container) {
        return new UserService($container->get(EntityManager::class));
    },

    PriviligesService::class => function (ContainerInterface $c) {
        return new PriviligesService($c->get(EntityManager::class));
    },

    SystemSettingService::class => function (ContainerInterface $c) {
        return new SystemSettingService($c->get(EntityManager::class));
    },

    DuesService::class => function (ContainerInterface $c) {
        return new DuesService($c->get(EntityManager::class));
    },

    ReceiptService::class => function (ContainerInterface $c) {
        return new ReceiptService($c->get(EntityManager::class));
    },

    LogsService::class => function (ContainerInterface $c) {
        return new LogsService($c->get(EntityManager::class));
    },

    Messages::class => function () {
        return new Messages();
    },

    LoginHistoryService::class => function (ContainerInterface $c) {
        return new LoginHistoryService($c->get(EntityManager::class));
    },

    TransactionService::class => function (ContainerInterface $c) {
        return new TransactionService($c->get(EntityManager::class));
    },

    AnnouncementService::class => function (ContainerInterface $c) {
        return new AnnouncementService($c->get(EntityManager::class));
    },

    PaymentService::class => function (ContainerInterface $c) {
        return new PaymentService($c->get(EntityManager::class));
    },

    TransactionLogsService::class => function (ContainerInterface $c) {
        return new TransactionLogsService($c->get(EntityManager::class));
    },

    Service::class => function (ContainerInterface $c) {
        return new Service($c->get(EntityManager::class));
    },

    IssuesService::class => function (ContainerInterface $c) {
        return new IssuesService($c->get(EntityManager::class));
    },

    CodeModelService::class => function (ContainerInterface $c) {
        return new CodeModelService($c->get(EntityManager::class));
    },

    \App\service\UserLogsService::class => function (ContainerInterface $c) {
        return new \App\service\UserLogsService($c->get(EntityManager::class));
    },

    \App\service\FundService::class => function (ContainerInterface $c) {
        return new \App\service\FundService($c->get(EntityManager::class));
    },

    \App\service\FundSourceService::class => function (ContainerInterface $c) {
        return new \App\service\FundSourceService($c->get(EntityManager::class));
    },

    \App\service\IncomeService::class => function (ContainerInterface $c) {
        return new \App\service\IncomeService($c->get(EntityManager::class));
    },

    \App\service\ExpenseService::class => function (ContainerInterface $c) {
        return new \App\service\ExpenseService($c->get(EntityManager::class));
    },

    \App\service\BillService::class => function (ContainerInterface $c) {
        return new \App\service\BillService($c->get(EntityManager::class));
    },

    \App\service\OverviewService::class => function (ContainerInterface $c) {
        return new \App\service\OverviewService($c->get(EntityManager::class));
    },

    AnnouncementHistoryService::class => function (ContainerInterface $c) {
        return new AnnouncementHistoryService($c->get(EntityManager::class));
    },

);