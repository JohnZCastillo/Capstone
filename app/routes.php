<?php

global $twig;

use Slim\App;
use Slim\Interfaces\RouteCollectorProxyInterface as Group;

return function (App $app) use ($twig) {

    $app->group('/admin', function (Group $group) {

        $group->get('/system',
            \App\controller\admin\system\ViewSettings::class
        )->setName('system');

        $group->post('/system',
            \App\controller\admin\system\UpdateSettings::class
        )->setName('system');

        $group->get('/payments', \App\controller\admin\payments\Homepage::class)
            ->setName('home');

        $group->post('/payments/add-due',
            \App\controller\admin\payments\AddDue::class
        )->setName('home');

        $group->post('/payments/year-dues',
            \App\controller\admin\payments\YearlyDue::class
        )->setName('home');

        $group->post('/payments/manual',
            \App\controller\admin\payments\ManualPayment::class)
            ->setName('home');

        $group->post('/payment-settings', \App\controller\admin\payments\PaymentSettings::class)
            ->setName('home');

        $group->post('/transaction/approve', \App\controller\admin\payments\ApprovePayment::class)
            ->setName('home');

        $group->post('/transaction/reject',
            \App\controller\admin\payments\RejectPayment::class
        )->setName('home');

        $group->get('/transaction/{id}', \App\controller\admin\payments\Transaction::class)
            ->setName('home');

        $group->post('/report',
            \App\controller\admin\report\PaidPaymentReport::class
        )->setName('home');

        $group->get('/issues',
            \App\controller\admin\issues\Issues::class
        )->setName('issues');

        $group->get('/issue/{id}',
            \App\controller\admin\issues\Issue::class
        )->setName('issues');

        $group->post('/issues/action',
            \App\controller\admin\issues\MakeAction::class
        )->setName('issues');

        $group->get('/announcements',
            \App\controller\admin\announcement\Announcements::class
        )->setName('announcements');

        $group->get('/announcement',
            \App\controller\admin\announcement\CreateAnnouncement::class
        )->setName('announcements');

        $group->get('/announcement/edit/{id}',
            \App\controller\admin\announcement\EditAnnouncement::class
        )->setName('announcements');

        $group->get('/announcement/edit/history/{id}',
            \App\controller\admin\announcement\EditHistoryAnnouncement::class
        )->setName('announcements');


        $group->post('/announcement/post',
            \App\controller\admin\announcement\MakeAnnouncement::class
        )->setName('announcements');

        $group->post('/announcement/archive/{id}',
            \App\controller\admin\announcement\ArchiveAnnouncement::class
        )->setName('announcements');

        $group->post('/announcement/post/{id}',
            \App\controller\admin\announcement\PostAnnouncement::class
        )->setName('announcements');

        $group->get('/logs',
            \App\controller\admin\logs\Logs::class
        )->setName('logs');

    })->add(\App\middleware\ActivePage::class);

};



//$app->get('/', [AdminController::class,'landingPage'])->add(\App\middleware\BypassHomepage::class);
//
//$app->get('/signupNotAllowed', function (Request $request, Response $response) use ($twig) {
//    return $twig->render($response, 'temporary.html');
//})->add(\App\middleware\BypassHomepage::class);
//
//$app->get('/denied', function (Request $request, Response $response) use ($twig) {
//    return $twig->render($response, 'denied.html');
//});
//
//$app->get('/terms-and-conditions', [AuthController::class, 'termsAndCondition']);
//
//$app->get('/blocked', function (Request $request, Response $response) use ($twig) {
//    return $twig->render($response, 'blockpage.html');
//});
//
//$app->get('/uploads/{image}', function ($request, $response, $args) {
//    return $response->withStatus(404)->write('Image not found');
//});
//
//$app->get('/forgot-password', function (Request $request, Response $response) use ($twig) {
//
//    if (\App\lib\Login::isLogin()) {
//        return $response
//            ->withHeader('Location', "/")
//            ->withStatus(302);
//    }
//
//    return $twig->render($response, '/pages/forgotten-password.html');
//});
//
//$app->get('/test', \App\controller\user\ViewHomepage::class);
//
//$app->post('/login', [AuthController::class, 'login']);
//$app->get('/logout', [AuthController::class, 'logout']);
//$app->post('/forgot-password', [AuthController::class, 'code']);
//$app->post('/new-code', [AuthController::class, 'newCode']);
//
//$app->get('/backup-restore', [BackupRestore::class, 'backupAndRestore']);
//
//$app->get('/backup-db', [BackupRestore::class, 'backup'])
//    ->add(OfflineAuthorize::class);
//
//$app->get('/restore-db', [BackupRestore::class, 'restore'])
//    ->add(OfflineAuthorize::class);
//
//$app->post('/restore-db-file', [BackupRestore::class, 'restoreFromFile'])
//    ->add(OfflineAuthorize::class);
//
//$app->post('/offline-login', [BackupRestore::class, 'backupAndRestore']);
//$app->get('/offline-login', [BackupRestore::class, 'offlineLogin']);
//
//$app->post('/register', [AuthController::class, 'register']);
//
//$app->get('/register', function (Request $request, Response $response) use ($twig) {
//    return $twig->render($response, 'pages/register.html');
//});
//
//$app->get('/login', function (Request $request, Response $response) use ($twig, $container) {
//    $flash = $container->get(\Slim\Flash\Messages::class);
//    $message = $flash->getFirstMessage('AuthFailedMessage');
//    return $twig->render($response, 'pages/login.html', [
//        'loginErrorMessage' => $message
//    ]);
//});
//
//$app->get('/invalid-session', function (Request $request, Response $response) use ($twig, $container) {
//
//    if (Login::isLogin()) {
//        return $response->withHeader('Location', '/')->withStatus(302);
//    }
//
//    return $twig->render($response, 'pages/login.html', [
//        'loginErrorMessage' => "Your session has been terminated for security reasons"
//    ]);
//});
//
//$app->get('/ui', function (Request $request, Response $response) use ($twig, $container) {
//
//    return $twig->render($response, 'user/pages/dues.html', [
//        'loginErrorMessage' => "Your session has been terminated for security reasons"
//    ]);
//});
//
//$app->group('', function ($app) use ($twig, $container) {
//
//    if (Login::isLogin()) {
//        $userService = $container->get(\App\service\UserService::class);
//        $loginUser = $userService->findById(Login::getLogin());;
//        $twig->getEnvironment()->addGlobal('login_user', $loginUser);
//    }
//
//    $flash = $container->get(\Slim\Flash\Messages::class);
//    $twig->getEnvironment()->addGlobal('errorMessage', $flash->getFirstMessage('errorMessage'));
//
//    $app->group('', function ($app) {
//
//        $app->get('/home', [UserController::class, 'home'])
//            ->setName('home');
//
//        $app->get('/receipt/{id}', [PaymentController::class, 'getReceipt']);
//        $app->get('/dues', [UserController::class, 'dues']);
//
//        $app->get('/issues', [UserController::class, 'issues'])
//            ->setName('issues');
//
//        $app->post('/issue', [UserController::class, 'issue']);
//
//        $app->get('/issue/archive/{id}', [UserController::class, 'archiveIssue']);
//        $app->get('/issue/unarchive/{id}', [UserController::class, 'unArchiveIssue']);
//
//        $app->get('/transaction/{id}', [UserController::class, 'transaction']);
//        $app->post('/pay', [PaymentController::class, 'userPay']);
//
//        $app->get('/announcements', [UserController::class, 'announcements'])
//            ->setName('announcements');
//
//        $app->get('/account', [UserController::class, 'accountSettings'])
//            ->setName('account');
//
//        $app->get('/issues/{id}', [UserController::class, 'manageIssue'])
//            ->setName('issues');
//
//    })->add(\App\middleware\UserAuth::class);
//
//    $app->group('/api', function ($app) {
//        $app->post('/add-due', [ApiController::class, 'addDue']);
//        $app->post('/year-dues', [ApiController::class, 'yearDues']);
//        $app->post('/user', [ApiController::class, 'user']);
//        $app->post('/change-password', [ApiController::class, 'changePassword']);
//        $app->post('/change-details', [ApiController::class, 'changeDetails']);
//    });
//
//    $app->group('/admin', function ($app) use ($twig) {
//
//        $app->get('/account', [AdminController::class, 'accountSettings']);
//
//        $app->group('', function ($app) use ($twig) {
//
//            $app->get('/home', [AdminController::class, 'home'])
//                ->setName('home');
//
//            $app->get('/transaction/{id}', [AdminController::class, 'transaction'])
//                ->setName('home');
//
//            $app->post('/transaction/reject', [AdminController::class, 'rejectPayment']);
//            $app->post('/transaction/approve', [AdminController::class, 'approvePayment']);
//            $app->post('/payment-settings', [AdminController::class, 'paymentSettings']);
//            $app->post('/report', [ReportController::class, 'report']);
//            $app->post('/manual-payment', [PaymentController::class, 'manualPayment']);
//
//
//
//        })->add(\App\middleware\AdminPaymentAuth::class);
//
//        $app->group('', function ($app) use ($twig) {
//
//
//            $app->post('/announcement', [AdminController::class, 'announcement']);
//
//            $app->get('/announcement/edit/{id}', [AdminController::class, 'editAnnouncement'])
//                ->setName('announcements');
//
//            $app->get('/announcement/delete/{id}', [AdminController::class, 'deleteAnnouncement'])
//                ->setName('announcements');
//
//
//            $app->get('/announcement/post/{id}', [AdminController::class, 'postAnnouncement'])
//                ->setName('announcements');
//
//            $app->get('/announcement/archive/{id}', [AdminController::class, 'archiveAnnouncement'])
//                ->setName('announcements');
//
//
//            $app->get('/announcements', [AdminController::class, 'announcements'])
//                ->setName('announcements');
//
//
//        })->add(\App\middleware\AdminAnnouncementAuth::class);
//
//        $app->group('', function ($app) use ($twig) {
//            $app->get('/issues', [AdminController::class, 'issues'])
//                ->setName('issues');
//
//            $app->get('/issues/{id}', [AdminController::class, 'manageIssue'])
//            ->setName('issues');
//
//            $app->post('/issues/action', [AdminController::class, 'actionIssue']);
//        })->add(\App\middleware\AdminIssuesAuth::class);
//
//
//        $app->group('', function ($app) use ($twig) {
//            $app->get('/users', [AdminController::class, 'users'])
//                ->setName('users');
//
//            $app->post('/block-user', [ApiController::class, 'blockUser']);
//            $app->post('/unblock-user', [ApiController::class, 'unblockUser']);
//        })->add(\App\middleware\AdminUsersAuth::class);
//
//        $app->group('', function ($app) use ($twig) {
//            $app->post('/manage-privileges', [AdminController::class, 'managePrivileges']);
//
//            $app->get('/logs', [AdminController::class, 'logs'])
//            ->setName('logs');
//
//            $app->get('/system', [AdminController::class, 'systemSettings'])
//                ->setName('system');
//
//            $app->post('/system', [AdminController::class, 'updateSystemSettings']);
//
//            $app->get('/overview', [AdminController::class, 'overview']);
//            $app->post('/overview', [AdminController::class, 'updateOverview']);
//            $app->post('/add-staff', [AdminController::class, 'addStaff']);
//            $app->post('/remove-staff', [AdminController::class, 'removeStaff']);
//
//            $app->get('/budget', [AdminController::class, 'budgetManagement']);
//            $app->get('/fund/{id}', [AdminController::class, 'fund']);
//            $app->post('/add-income/{id}', [AdminController::class, 'addIncome']);
//            $app->post('/add-expense/{id}', [AdminController::class, 'addExpense']);
//            $app->post('/new-bill', [AdminController::class, 'addBill']);
//            $app->post('/approve-expense/{id}', [AdminController::class, 'approveExpense']);
//            $app->post('/reject-expense/{id}', [AdminController::class, 'rejectExpense']);
//            $app->post('/new-fund', [AdminController::class, 'newFund']);
//            $app->post('/archive-fund', [AdminController::class, 'archiveFund']);
//            $app->post('/archive-bill/{id}', [AdminController::class, 'archiveBill']);
//            $app->post('/active-bill/{id}', [AdminController::class, 'activeBill']);
//            $app->post('/edit-bill', [AdminController::class, 'editBill']);
//            $app->post('/active-fund', [AdminController::class, 'activeFund']);
//            $app->get('/bill/{id}', [ApiController::class, 'findBill']);
//            $app->post('/bill/generate', [ApiController::class, 'generateBill']);
//            $app->post('/approve-bill/{id}', [AdminController::class, 'approveBillExpense']);
//            $app->post('/reject-bill/{id}', [AdminController::class, 'rejectBillExpense']);
//
//        })->add(\App\middleware\SuperAdminAuth::class);
//
//
//    })->add(ActivePage::class)->add(\App\middleware\AdminAuth::class);
//
//    $app->post('/upload', [ApiController::class, 'upload']);
//    $app->post('/payable-amount', [ApiController::class, 'amount']);
//    $app->post('/api/force-logout', [ApiController::class, 'forceLogout']);
//    $app->get('/admin/announcement', [AdminController::class, 'announcementPage']);
//
//})->add(ActivePage::class)->add(ForceLogout::class)->add(Auth::class);
