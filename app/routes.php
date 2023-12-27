<?php

global $twig;

use Slim\App;

return function (App $app) use($twig){

    $publicRoute = require __DIR__ . '/public/route.php';

    $accountRoute = require __DIR__ . '/admin/account/route.php';
    $announcementRoute = require __DIR__ . '/admin/announcements/route.php';
    $budgetRoute = require __DIR__ . '/admin/budget/route.php';
    $issuesRoute = require __DIR__ . '/admin/issues/route.php';
    $logsRoute = require __DIR__ . '/admin/logs/route.php';
    $overviewRoute = require __DIR__ . '/admin/overview/route.php';
    $paymentRoute = require __DIR__ . '/admin/payments/route.php';
    $settingsRoute = require __DIR__ . '/admin/settings/route.php';
    $usersRoute = require __DIR__ . '/admin/users/route.php';

    $publicRoute($app);

    $accountRoute($app);
    $announcementRoute($app);
    $budgetRoute($app);
    $issuesRoute($app);
    $logsRoute($app);
    $overviewRoute($app);
    $paymentRoute($app);
    $settingsRoute($app);
    $usersRoute($app);

};