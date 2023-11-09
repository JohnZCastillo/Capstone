<?php

session_start();

use App\controller\AdminController;
use App\controller\ApiController;
use App\controller\AuthController;
use App\controller\PaymentController;
use App\controller\ReportController;
use App\controller\UserController;
use App\lib\Login;
use App\lib\ReportMaker;
use App\middleware\Auth;
use App\middleware\ForceLogout;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Factory\AppFactory;
use Slim\Views\Twig;
use Slim\Views\TwigMiddleware;
use UMA\DIC\Container;

require './vendor/autoload.php';

date_default_timezone_set("Asia/Manila");

/** @var Container $container */
$container = require_once __DIR__ . '/bootstrap.php';

AppFactory::setContainer($container);

$app = AppFactory::create();

// Configure Twig view renderer
$twig = Twig::create('./src/views/', ['cache' => false, 'debug' => true]);
$twig->addExtension(new \Twig\Extension\DebugExtension());
$twig->getEnvironment()->getExtension(\Twig\Extension\CoreExtension::class)->setTimezone('Asia/Manila');

$app->add(TwigMiddleware::create($app, $twig));

$app->get('/', function (Request $request, Response $response) use ($twig) {
    return $twig->render($response, 'homepage.html');
})->add(\App\middleware\BypassHomepage::class);

$app->get('/signupNotAllowed', function (Request $request, Response $response) use ($twig) {
    return $twig->render($response, 'temporary.html');
})->add(\App\middleware\BypassHomepage::class);


$app->get('/denied', function (Request $request, Response $response) use ($twig) {
    return $twig->render($response, 'denied.html');
});

$app->get('/terms-and-conditions', [AuthController::class, 'termsAndCondition']);

$app->get('/blocked', function (Request $request, Response $response) use ($twig) {
    return $twig->render($response, 'blockpage.html');
});


$app->get('/uploads/{image}', function ($request, $response, $args) {
    return $response->withStatus(404)->write('Image not found');
});


$app->get('/forgot-password', function (Request $request, Response $response) use ($twig) {

    if (\App\lib\Login::isLogin()) {
        return $response
            ->withHeader('Location', "/")
            ->withStatus(302);
    }

    return $twig->render($response, '/pages/forgotten-password.html');
});

$app->get('/test', [AdminController::class, 'test']);

// Protected Routes
$app->group('', function ($app)  use ($twig) {


    $app->group('', function ($app) {

        $app->get('/home', [UserController::class, 'home']);
        $app->get('/receipt/{id}', [UserController::class, 'receipt']);
        $app->get('/dues', [UserController::class, 'dues']);
        $app->get('/issues', [UserController::class, 'issues']);
        $app->post('/issue', [UserController::class, 'issue']);
        $app->get('/issue/archive/{id}', [UserController::class, 'archiveIssue']);
        $app->get('/issue/unarchive/{id}', [UserController::class, 'unArchiveIssue']);

        $app->get('/transaction/{id}', [UserController::class, 'transaction']);
        $app->post('/pay', [PaymentController::class, 'userPay']);
        $app->get('/announcements', [UserController::class, 'announcements']);

        $app->get('/account', [UserController::class, 'accountSettings']);

        $app->get('/issues/{id}', [UserController::class, 'manageIssue']);

    })->add(\App\middleware\UserAuth::class);


    $app->group('/api', function ($app) {
        $app->post('/add-due', [ApiController::class, 'addDue']);
        $app->post('/year-dues', [ApiController::class, 'yearDues']);
        $app->post('/user', [ApiController::class, 'user']);
        $app->post('/change-password', [ApiController::class, 'changePassword']);
        $app->post('/change-details', [ApiController::class, 'changeDetails']);
    });

    $app->group('/admin', function ($app) use ($twig) {
        $app->get('/account', [AdminController::class, 'accountSettings']);
    })->add(\App\middleware\AdminAuth::class)->add(Auth::class);

    $app->group('/admin', function ($app) use ($twig) {
        $app->get('/home', [AdminController::class, 'home']);

        $app->get('/test', [AdminController::class, 'test']);

        $app->get('/transaction/{id}', [AdminController::class, 'transaction']);
        $app->post('/transaction/reject', [AdminController::class, 'rejectPayment']);
        $app->post('/transaction/approve', [AdminController::class, 'approvePayment']);
        $app->post('/payment-settings', [AdminController::class, 'paymentSettings']);
        $app->get('/payment-map', [AdminController::class, 'paymentMap']);
        $app->post('/report', [ReportController::class, 'report']);
        $app->post('/manual-payment', [PaymentController::class, 'manualPayment']);
        $app->post('/block-user', [ApiController::class, 'blockUser']);
        $app->post('/unblock-user', [ApiController::class, 'unblockUser']);

    })->add(\App\middleware\AdminPaymentAuth::class)->add(\App\middleware\AdminAuth::class)->add(Auth::class);

    $app->group('/admin', function ($app) use ($twig) {

        $app->post('/announcement', [AdminController::class, 'announcement']);

        $app->get('/announcement/edit/{id}', [AdminController::class, 'editAnnouncement']);
        $app->get('/announcement/delete/{id}', [AdminController::class, 'deleteAnnouncement']);
        $app->get('/announcement/post/{id}', [AdminController::class, 'postAnnouncement']);
        $app->get('/announcement/archive/{id}', [AdminController::class, 'archiveAnnouncement']);

        $app->get('/announcements', [AdminController::class, 'announcements']);

    })->add(\App\middleware\AdminAnnouncementAuth::class)->add(\App\middleware\AdminAuth::class)->add(Auth::class);

    $app->group('/admin', function ($app) use ($twig) {
        $app->get('/issues', [AdminController::class, 'issues']);
        $app->get('/issues/{id}', [AdminController::class, 'manageIssue']);
        $app->post('/issues/action', [AdminController::class, 'actionIssue']);
    })->add(\App\middleware\AdminIssuesAuth::class)->add(\App\middleware\AdminAuth::class)->add(Auth::class);

    $app->group('/admin', function ($app) use ($twig) {
        $app->get('/users', [AdminController::class, 'users']);
    })->add(\App\middleware\AdminUsersAuth::class)->add(\App\middleware\AdminAuth::class)->add(Auth::class);


    $app->group('/admin', function ($app) use ($twig) {
        $app->post('/manage-privileges', [AdminController::class, 'managePrivileges']);
        $app->get('/logs', [AdminController::class, 'logs']);
        $app->get('/system',[AdminController::class, 'systemSettings']);
        $app->post('/system',[AdminController::class, 'updateSystemSettings']);
    })->add(\App\middleware\SuperAdminAuth::class)->add(Auth::class);

    $app->post('/upload', [ApiController::class, 'upload']);
    $app->post('/payable-amount', [ApiController::class, 'amount']);

    $app->post('/api/force-logout', [ApiController::class, 'forceLogout']);

    $app->get('/admin/announcement',[AdminController::class,'announcementPage']);

})->add(ForceLogout::class)->add(Auth::class);

// Public Routes
$app->post('/login', [AuthController::class, 'login']);
$app->get('/logout', [AuthController::class, 'logout']);
$app->post('/forgot-password', [AuthController::class, 'code']);
$app->post('/new-code', [AuthController::class, 'newCode']);

$app->post('/register', [AuthController::class, 'register']);

// Return Signup View
$app->get('/register', function (Request $request, Response $response) use ($twig) {
    return $twig->render($response, 'pages/register.html');
});


// Return Login View
$app->get('/login', function (Request $request, Response $response) use ($twig, $container) {
    $flash = $container->get(\Slim\Flash\Messages::class);
    $message = $flash->getFirstMessage('AuthFailedMessage');
    return $twig->render($response, 'pages/login.html', [
        'loginErrorMessage' => $message
    ]);
});

$app->get('/invalid-session', function (Request $request, Response $response) use ($twig, $container) {

    if(Login::isLogin()){
        return $response->withHeader('Location', '/')->withStatus(302);
    }


    return $twig->render($response, 'pages/login.html', [
        'loginErrorMessage' => "Your session has been terminated for security reasons"
    ]);
});



$app->run();