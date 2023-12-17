<?php

namespace App\controller;

use App\lib\Filter;
use App\lib\Helper;
use App\lib\Image;
use App\lib\Login;
use App\lib\Time;
use App\model\AnnouncementModel;
use App\model\budget\BillModel;
use App\model\budget\ExpenseModel;
use App\model\budget\FundModel;
use App\model\budget\IncomeModel;
use App\model\enum\AnnouncementStatus;
use App\model\enum\BudgetStatus;
use App\model\enum\UserRole;
use App\model\LogsModel;
use App\model\overview\Staff;
use App\model\PaymentModel;
use DateTime;
use Exception;
use Respect\Validation\Validator as V;
use Slim\Views\Twig;

class AdminController extends Controller
{

    public function home($request, $response, $args)
    {

        // get the query params
        $queryParams = $request->getQueryParams();

        $settings = $this->paymentService->findById(1);

        // if page is present then set value to page otherwise to 1
        $page = $queryParams['page'] ?? 1;

        $filter = Filter::check($queryParams);

        $id = empty($queryParams['query']) ? null : $queryParams['query'];

        // max transaction per page
        $max = 5;

        $view = Twig::fromRequest($request);

        //Get Transaction
        $result = $this->transactionService->adminGetAll($page, $max, $id, $filter);

        $startOfPaymentYear = Time::getYearFromStringDate($this->getPaymentSettings()->getStart());

        $dues = $this->getDues($startOfPaymentYear);

        $errorMessage = $this->flashMessages->getFirstMessage('errorMessage');

        $data = [
            'paymentYear' => Time::getYearSpan($startOfPaymentYear),
            'paymentStart' => $startOfPaymentYear,
            'dues' => $dues ?? null,
            'transactions' => $result->getItems(),
            'currentPage' => $page,
            'query' => $id,
            'from' => $queryParams['from'] ?? null,
            'to' => $queryParams['to'] ?? null,
            'status' => $queryParams['status'] ?? null,
            'settings' => $settings,
            'paginator' => $result,
            'loginUser' => $this->getLogin(),
            "errorMessage" => $errorMessage,
        ];

        return $view->render($response, 'admin/pages/payments.html', $data);
    }

    public function transaction($request, $response, $args)
    {

        $view = Twig::fromRequest($request);

        $transaction = $this->transactionService->findById($args['id']);

        $user = $transaction->getUser();

        return $view->render($response, 'admin/pages/transaction.html', [
            'transaction' => $transaction,
            'receipts' => $transaction->getReceipts(),
            'user' => $user,
            'loginUser' => $this->getLogin(),
        ]);
    }

    public function rejectPayment($request, $response, $args)
    {

        $view = Twig::fromRequest($request);

        $id = $request->getParsedBody()['id'];
        $message = $request->getParsedBody()['message'];

        // get the transaction form db
        $transaction = $this->transactionService->findById($id);

        //login admin who rejected the payment
        $user = $this->getLogin();

        // set transctio to rejected
        $transaction->setStatus('REJECTED');

        $transaction->setRejectedBy($user);

        // save transaction
        $this->transactionService->save($transaction);

        //save logs
        $this->logsService->log($transaction, $user, $message, 'REJECTED');

        $action = "Payment with id of " . $transaction->getId() . " was rejected";

        $actionLog = new LogsModel();
        $actionLog->setAction($action);
        $actionLog->setTag("Payment");
        $actionLog->setUser($this->getLogin());
        $actionLog->setCreatedAt(new DateTime());

        $this->actionLogs->addLog($actionLog);

        return $response
            ->withHeader('Location', "/admin/transaction/$id")
            ->withStatus(302);
    }

    public function approvePayment($request, $response, $args)
    {

        $view = Twig::fromRequest($request);

        $id = $request->getParsedBody()['id'];

        $message = "Payment was approved";

        // get the transaction form db
        $transaction = $this->transactionService->findById($id);

        //login admin who approved the payment
        $user = $this->getLogin();

        //array of reference number
        $fields = $request->getParsedBody()['field'];

        //array of transaction receipts
        $reciepts = $transaction->getReceipts();

        for ($i = 0; $i < count($reciepts); $i++) {
            $this->receiptService->confirm($reciepts[$i], $fields[$i]);
        }

        // set transction
        $transaction->setStatus('APPROVED');

        $transaction->setApprovedBy($user);

        // save transaction
        $this->transactionService->save($transaction);

        //save logs
        $this->logsService->log($transaction, $user, $message, 'APPROVED');

        $action = "Payment with id of " . $transaction->getId() . " was approved";

        $actionLog = new LogsModel();
        $actionLog->setAction($action);
        $actionLog->setTag("Payment");
        $actionLog->setUser($this->getLogin());
        $actionLog->setCreatedAt(new DateTime());

        $this->actionLogs->addLog($actionLog);

        return $response
            ->withHeader('Location', "/admin/transaction/$id")
            ->withStatus(302);
    }

    public function paymentSettings($request, $response, $args)
    {

        $view = Twig::fromRequest($request);

        $id = $request->getParsedBody()['id'];
        $name = $request->getParsedBody()['name'];
        $number = $request->getParsedBody()['number'];
        $start = $request->getParsedBody()['start'];

        $settings = new PaymentModel();

        //find settings if id is not null
        if ($id != null) {
            $settings = $this->paymentService->findById($id);
        }

        //update qr
        if (isset($_FILES['qr']) && $_FILES['qr']['error'] === UPLOAD_ERR_OK) {
            $path = './uploads/';
            $settings->setQr(Image::store($path, $_FILES['qr']));
        }

        $settings->setAccountName($name);
        $settings->setAccountNumber($number);
        $settings->setStart(Time::startMonth($start));

        $this->paymentService->save($settings);

        $action = "Payment settings was update";

        $actionLog = new LogsModel();
        $actionLog->setAction($action);
        $actionLog->setTag("Payment Settings");
        $actionLog->setUser($this->getLogin());
        $actionLog->setCreatedAt(new DateTime());
        $this->actionLogs->addLog($actionLog);

        return $response
            ->withHeader('Location', "/admin/home")
            ->withStatus(302);
    }

    public function announcement($request, $response, $args)
    {

        $view = Twig::fromRequest($request);

        $title = $request->getParsedBody()['title'];
        $content = $request->getParsedBody()['content'];
        $id = $request->getParsedBody()['id'];

        $post = new AnnouncementModel();

        $post->setCreatedAt(Time::timestamp());
        $post->setUser($this->getLogin());

        $action = "Announcement with id of " . $post->getId() . " was created";

        if (Helper::existAndNotNull($id)) {
            $post = $this->announcementService->findById($id);
            $action = "Announcement with id of " . $post->getId() . " was edited";
            $this->flashMessages->addMessage('message', 'Announcement ' . $post->getTitle() . ' edited');
        } else {
            $this->flashMessages->addMessage('message', 'Announcement ' . $post->getTitle() . ' Posted');
        }

        $post->setTitle($title);
        $post->setContent($content);
        $post->setStatus(AnnouncementStatus::posted());

        try {
            $this->announcementService->save($post);
        } catch (\Throwable $th) {
            $this->flashMessages->addMessage('message', 'Announcement ' . $post->getTitle() . 'Posting Error');
        }


        $actionLog = new LogsModel();
        $actionLog->setAction($action);
        $actionLog->setTag("Announcement");
        $actionLog->setUser($this->getLogin());
        $actionLog->setCreatedAt(new DateTime());
        $this->actionLogs->addLog($actionLog);

        return $response->withHeader('Location', "/admin/announcements")
            ->withStatus(302);
    }

    public function deleteAnnouncement($request, $response, $args)
    {

        // get the query params
        $queryParams = $request->getQueryParams();

        $view = Twig::fromRequest($request);

        $id = $args['id'];

        $post = $this->announcementService->findById($id);

        $this->flashMessages->addMessage('message', 'Announcement ' . $post->getTitle() . ' deleted');

        $action = "Announcement with id of " . $post->getId() . " was deleted";

        $actionLog = new LogsModel();
        $actionLog->setAction($action);
        $actionLog->setTag("Announcement");
        $actionLog->setUser($this->getLogin());
        $actionLog->setCreatedAt(new DateTime());
        $this->actionLogs->addLog($actionLog);

        $this->announcementService->delete($post);

        return $response
            ->withHeader('Location', '/admin/announcements')
            ->withStatus(302);
    }

    public function editAnnouncement($request, $response, $args)
    {

        $view = Twig::fromRequest($request);

        $id = $args['id'];

        $announcement = $this->announcementService->findById($id);

        return $view->render($response, 'admin/pages/announcement.html', [
            'announcement' => $announcement,
            'loginUser' => $this->getLogin(),
        ]);
    }

    public function postAnnouncement($request, $response, $args)
    {

        $view = Twig::fromRequest($request);

        $id = $args['id'];

        $announcement = $this->announcementService->findById($id);

        $announcement->setStatus(AnnouncementStatus::posted());

        $this->announcementService->save($announcement);

        $this->flashMessages->addMessage('Test', 'This is a message');

        $action = "Announcement with id of " . $announcement->getId() . " status was set to posted";

        $actionLog = new LogsModel();
        $actionLog->setAction($action);
        $actionLog->setTag("Announcement");
        $actionLog->setUser($this->getLogin());
        $actionLog->setCreatedAt(new DateTime());
        $this->actionLogs->addLog($actionLog);

        return $response
            ->withHeader('Location', "/admin/announcements?status=ARCHIVED")
            ->withStatus(302);
    }

    public function archiveAnnouncement($request, $response, $args)
    {

        $view = Twig::fromRequest($request);

        $id = $args['id'];

        $announcement = $this->announcementService->findById($id);

        $announcement->setStatus(AnnouncementStatus::archived());

        $this->announcementService->save($announcement);

        $this->flashMessages->addMessage('Test', 'This is a message');

        $action = "Announcement with id of " . $announcement->getId() . " status was set to archived";

        $actionLog = new LogsModel();
        $actionLog->setAction($action);
        $actionLog->setTag("Announcement");
        $actionLog->setUser($this->getLogin());
        $actionLog->setCreatedAt(new DateTime());
        $this->actionLogs->addLog($actionLog);


        return $response
            ->withHeader('Location', "/admin/announcements?status=POSTED")
            ->withStatus(302);
    }


    public function announcements($request, $response, $args)
    {

        $message = $this->flashMessages->getFirstMessage('message');

        $view = Twig::fromRequest($request);

        // get the query params
        $queryParams = $request->getQueryParams();

        // if page is present then set value to page otherwise to 1
        $page = isset($queryParams['page']) ? $queryParams['page'] : 1;

        $id = isset($queryParams['query']) ? $queryParams['query'] : null;

        $status = isset($queryParams['status']) ? $queryParams['status'] : 'posted';

        // max transaction per page
        $max = 5;

        $filter = Filter::check($queryParams);

        $result = $this->announcementService->getAll($page, $max, null, $filter, null, $status);

        return $view->render($response, 'admin/pages/announcements.html', [
            'announcements' => $result['announcements'],
            'message' => $message,
            'query' => $id,
            'currentPage' => $page,
            'from' => isset($queryParams['from']) ? $queryParams['from'] : null,
            'to' => isset($queryParams['to']) ? $queryParams['to'] : null,
            'totalPages' => ceil(($result['totalAnnouncement']) / $max),
            'status' => $status,
            'loginUser' => $this->getLogin(),
        ]);
    }


    /**
     * View Issues.
     */
    public function issues($request, $response, $args)
    {

        $message = $this->flashMessages->getFirstMessage('message');

        $view = Twig::fromRequest($request);

        $queryParams = $request->getQueryParams();

        // if page is present then set value to page otherwise to 1
        $page = $queryParams['page'] ?? 1;

        $type = $queryParams['type'] ?? 'posted';

        // max transaction per page
        $max = 3;

        $filter = Filter::check($queryParams);

        $createdAt = empty($queryParams['createdAt']) ? null : $queryParams['createdAt'];

        $query = empty($queryParams['query']) ? null : $queryParams['query'];

        $pagination = $this->issuesService->getAll($page, $max, $query, $filter, null, $type, $createdAt);

        return $view->render($response, 'admin/pages/issues.html', [
            'type' => $type,
            'message' => $message,
            'issues' => $pagination->getItems(),
            'currentPage' => $page,
            'status' => $queryParams['status'] ?? null,
            'paginator' => $pagination,
            'createdAt' => $createdAt,
            'loginUser' => $this->getLogin(),
        ]);
    }

    public function users($request, $response, $args)
    {

        $view = Twig::fromRequest($request);

        $queryParams = $request->getQueryParams();

        $errorMessage = $this->flashMessages->getFirstMessage('errorMessage');

        // if page is present then set value to page otherwise to 1
        $page = $queryParams['page'] ?? 1;

        $role = $queryParams['role'] ?? 'admin';

        // max transaction per page
        $max = 3;

        $filter = Filter::check($queryParams);

        $query = empty($queryParams['query']) ? "" : $queryParams['query'];

        $pagination = $this->userSerivce->getAll($page, $max, $query, $filter, $role);

        return $view->render($response, 'admin/pages/users.html', [
            'users' => $pagination->getItems(),
            'currentPage' => $page,
            'role' => $role,
            'paginator' => $pagination,
            'superAdmin' => $this->getLogin()->getRole() === "super",
            'loginUser' => $this->getLogin(),
            'query' => $query,
            "errorMessage" => $errorMessage,
        ]);
    }

    /**
     * View Issues.
     */
    public function manageIssue($request, $response, $args)
    {

        $id = $args['id'];

        $view = Twig::fromRequest($request);

        //might throw and error
        $issue = $this->issuesService->findById($id);

        return $view->render($response, 'admin/pages/issue.html', [
            'issue' => $issue,
        ]);
    }


    public function actionIssue($request, $response, $args)
    {

        $content = $request->getParsedBody();

        $id = $content['id'];

        $issue = $this->issuesService->findById($id);

        $issue->setAction($content['action']);

        $issue->setStatus($content['status']);

        $this->issuesService->save($issue);

        return $response
            ->withHeader('Location', "/admin/issues/$id")
            ->withStatus(302);
    }


    /**
     * View Issues.
     */
    public function paymentMap($request, $response, $args)
    {

        $message = $this->flashMessages->getFirstMessage('message');

        $view = Twig::fromRequest($request);

        $queryParams = $request->getQueryParams();

        // if page is present then set value to page otherwise to 1
        $page = isset($queryParams['page']) ? $queryParams['page'] : 1;

        $type = isset($queryParams['type']) ? $queryParams['type'] : 'posted';

        // max transaction per page
        $max = 5;

        $filter = Filter::check($queryParams);

        return $view->render($response, 'pages/admin-payments-map.html', [
            'type' => $type,
            'message' => $message,
            'currentPage' => $page,
            'from' => isset($queryParams['from']) ? $queryParams['from'] : null,
            'to' => isset($queryParams['to']) ? $queryParams['to'] : null,
            'status' => isset($queryParams['status']) ? $queryParams['status'] : null,
            // 'totalPages' => ceil(($result['totalIssues']) / $max),
        ]);
    }

    public function accountSettings($request, $response, $args)
    {

        $user = $this->getLogin();
        $name = $user->getName();
        $email = $user->getEmail();
        $block = $user->getBlock();
        $lot = $user->getLot();

        $loginHistory = $this->loginHistoryService->getLogs($user);
        $currentSession = session_id();

        $view = Twig::fromRequest($request);

        return $view->render($response, 'admin/pages/account.html', [
            "loginHistory" => $loginHistory,
            "sessionId" => $currentSession,
            "name" => $name,
            "email" => $email,
            "block" => $block,
            "lot" => $lot,
            "user" => $user,
            "logs" => $user->getMyLogs(),
        ]);
    }

    public function managePrivileges($request, $response, $args)
    {

        $params = $request->getParsedBody();

        try {

            if (!isset($params['email'])) {
                throw  new Exception("Email is required");
            }

            $email = $params['email'];

            if (!V::email()->validate($email)) {
                throw new Exception('Invalid Email');
            }

            $user = $this->userSerivce->findByEmail($email);

            if ($user == null) {
                throw  new Exception("User not Found!");
            }

            $admin = false;

            $managePayments = $params['payment'] ?? null;
            $manageIssues = $params['issue'] ?? null;
            $manageAnnouncements = $params['announcement'] ?? null;
            $manageUsers = $params['user'] ?? null;

            $user->setRole(UserRole::admin());
            $this->userSerivce->save($user);

            if (isset($managePayments)) {
                $user->getPrivileges()->setAdminPayment(true);
                $admin = true;
            } else {
                $user->getPrivileges()->setAdminPayment(false);
            }

            if (isset($manageIssues)) {
                $user->getPrivileges()->setAdminIssues(true);
                $admin = true;

            } else {
                $user->getPrivileges()->setAdminIssues(false);
            }

            if (isset($manageAnnouncements)) {
                $user->getPrivileges()->setAdminAnnouncement(true);
                $admin = true;

            } else {
                $user->getPrivileges()->setAdminAnnouncement(false);

            }

            if (isset($manageUsers)) {
                $user->getPrivileges()->setAdminUser(true);
                $admin = true;

            } else {
                $user->getPrivileges()->setAdminUser(false);
            }

            if ($admin) {
                $user->setRole(UserRole::admin());
            } else {
                $user->setRole(UserRole::user());
            }

            $this->userSerivce->save($user);

            $action = "User with id of " . $user->getId() . " update privileges";

            $actionLog = new LogsModel();
            $actionLog->setAction($action);
            $actionLog->setTag("Admin");
            $actionLog->setUser($this->getLogin());
            $actionLog->setCreatedAt(new DateTime());
            $this->actionLogs->addLog($actionLog);

            $this->priviligesService->save($user->getPrivileges());

            return $response
                ->withHeader('Location', "/admin/users")
                ->withStatus(302);
        } catch (Exception $e) {
            $this->flashMessages->addMessage('errorMessage', $e->getMessage());

            return $response
                ->withHeader('Location', "/admin/users")
                ->withStatus(302);
        }

    }


    public function systemSettings($request, $response, $args)
    {

        $systemSettings = $this->systemSettingService->findById();

        $twig = Twig::fromRequest($request);

        $user = $this->getLogin();

        $timezone = date_default_timezone_get();

        $errorMessage = $this->flashMessages->getFirstMessage('errorMessage');
        $successMessage = $this->flashMessages->getFirstMessage('successMessage');

        return $twig->render($response, 'admin/pages/system.html', [
            'timezone' => $timezone,
            "loginUser" => $user,
            "systemSettings" => $systemSettings,
            'errorMessage' => $errorMessage,
            'successMessage' => $successMessage,
        ]);

    }

    public function announcementPage($request, $response, $args)
    {

        $twig = Twig::fromRequest($request);

        $user = $this->getLogin();

        return $twig->render($response, 'admin/pages/announcement.html', [
            "loginUser" => $user
        ]);

    }

    public function logs($request, $response, $args)
    {
        $twig = Twig::fromRequest($request);

        $queryParams = $request->getQueryParams();

        // if page is present then set value to page otherwise to 1
        $page = $queryParams['page'] ?? 1;

        $filter['from'] = empty($queryParams['from']) ? null : (new DateTime($queryParams['from']))->format('Y-m-d H:i:s');
        $filter['to'] = empty($queryParams['to']) ? null : (new DateTime($queryParams['to']))->format('Y-m-d H:i:s');
        $filter['tag'] = null;


        $user = null;

        if (isset($queryParams['email'])) {
            $user = $this->userSerivce->findByEmail($queryParams['email']);
        }

        // max transaction per page
        $max = 10;

//        Get Transaction
        $result = $this->actionLogs->getAll($page, $max, $filter, $user);

        $data = [
            'logs' => $result->getItems(),
            'currentPage' => $page,
            'from' => $queryParams['from'] ?? null,
            'to' => $queryParams['to'] ?? null,
            'user' => $user,
            'status' => $queryParams['status'] ?? null,
            'paginator' => $result,
            'loginUser' => $this->getLogin(),
        ];

        return $twig->render($response, 'admin/pages/logs.html', $data);
    }

    public function test($request, $response, $args)
    {
        var_dump(
            $this->fundService->getMonthlyTally(1, 2023)
        );
    }

    public function updateSystemSettings($request, $response, $args)
    {

        $content = $request->getParsedBody();

        $systemSettings = $this->systemSettingService->findById();

        $systemSettings->setTermsAndCondition($content['termsAndCondition']);
        $systemSettings->setMailHost($content['mailHost']);
        $systemSettings->setMailUsername($content['mailUsername']);

//        only update password if not empty
        if (!empty($content['mailPassword'])) {
            $systemSettings->setMailPassword($content['mailPassword']);
        }

        if (isset($content['allowSignup'])) {
            $systemSettings->setAllowSignup(true);
        } else {
            $systemSettings->setAllowSignup(false);
        }

        $this->systemSettingService->save($systemSettings);

        return $response
            ->withHeader('Location', "/admin/system")
            ->withStatus(302);
    }

    public function newFund($request, $response, $args)
    {

        $twig = Twig::fromRequest($request);

        $content = $request->getParsedBody();

        $fund = new FundModel();
        $fund->setTitle($content['title']);
        $this->fundService->save($fund);

        return $response
            ->withHeader('Location', "/admin/budget")
            ->withStatus(302);
    }

    public function archiveFund($request, $response, $args)
    {

        $twig = Twig::fromRequest($request);

        $content = $request->getParsedBody();

        $fund = $this->fundService->findById($content['id']);

        try {

            if (!isset($fund)) {
                throw new Exception('Unable To Find Fund with ID of ' . $content['id']);
            }

            if ($fund->isMainFund()) {
                throw new Exception("Cannot Archive Main Fund");
            }

            $fund->setIsArchived(true);
            $this->fundService->save($fund);

        } catch (Exception $e) {
            $this->flashMessages->addMessage('errorMessage', $e->getMessage());
        }

        return $response
            ->withHeader('Location', '/admin/budget')
            ->withStatus(302);
    }

    public function archiveBill($request, $response, $args)
    {

        $twig = Twig::fromRequest($request);

        $bill = $this->billService->findById($args['id']);

        try {

            if (!isset($bill)) {
                throw new Exception('Unable To Find Bill');
            }

            $bill->setIsArchived(true);
            $this->billService->save($bill);

        } catch (Exception $e) {
            $this->flashMessages->addMessage('errorMessage', $e->getMessage());
        }

        return $response
            ->withHeader('Location', '/admin/budget')
            ->withStatus(302);
    }

    public function activeBill($request, $response, $args)
    {

        $twig = Twig::fromRequest($request);

        $bill = $this->billService->findById($args['id']);

        try {

            if (!isset($bill)) {
                throw new Exception('Unable To Find Bill');
            }

            $bill->setIsArchived(false);
            $this->billService->save($bill);

        } catch (Exception $e) {
            $this->flashMessages->addMessage('errorMessage', $e->getMessage());
        }

        return $response
            ->withHeader('Location', '/admin/budget')
            ->withStatus(302);
    }

    public function editBill($request, $response, $args)
    {

        $twig = Twig::fromRequest($request);

        $content = $request->getParsedBody();

        $bill = $this->billService->findById($content['billId']);
        $fund = $this->fundService->findById($content['fund']);

        try {

            if (!isset($bill)) {
                throw new Exception('Unable To Find Bill');
            }

            if (!isset($fund)) {
                throw new Exception('Unable To Find Fund');
            }


            $expense = $bill->getExpense();
            $expense->setTitle($content['title']);
            $expense->setFund($fund);
            $expense->setAmount($content['amount']);
            $expense->setPurpose($content['purpose']);

            $this->expenseService->save($expense);

        } catch (Exception $e) {
            $this->flashMessages->addMessage('errorMessage', $e->getMessage());
        }

        return $response
            ->withHeader('Location', '/admin/budget')
            ->withStatus(302);
    }

    public function activeFund($request, $response, $args)
    {

        $twig = Twig::fromRequest($request);

        $content = $request->getParsedBody();
        $query = $request->getQueryParams();

        $fund = $this->fundService->findById($content['id']);

        try {

            if (!isset($fund)) {
                throw new Exception('Unable To Find Fund with ID of ' . $content['id']);
            }

            if ($fund->isMainFund()) {
                throw new Exception("Cannot Archive Main Fund");
            }

            $fund->setIsArchived(false);
            $this->fundService->save($fund);

        } catch (Exception $e) {
            $this->flashMessages->addMessage('errorMessage', $e->getMessage());
        }

        return $response
            ->withHeader('Location', '/admin/budget?status=archived')
            ->withStatus(302);
    }

    public function fund($request, $response, $args)
    {

        $twig = Twig::fromRequest($request);

        $content = $request->getParsedBody();

        $id = $args['id'];

        $fund = $this->fundService->findById($id);
        $fundSources = $this->fundSourceService->getAll();

        try {

            if (!isset($fund)) {
                throw new Exception('Unable To Find Fund with ID of ' . $content['id']);
            }

            $year = (new DateTime())->format('Y');

            $expenses = $this->fundService->getYearlyExpenses($fund->getId(),$year);
            $incomes = $this->fundService->getYearlyIncome($fund->getId(), $year);
            $keys = $this->fundService->getKeys($year);

            return $twig->render($response, 'admin/pages/fund-details.html', [
                'fund' => $fund,
                'fundSources' => $fundSources,
                'yearlyExpenses' =>  array_values($expenses),
                'yearlyIncomes' =>  array_values($incomes),
                'keys' => $keys,
            ]);

        } catch (Exception $e) {
            $this->flashMessages->addMessage('errorMessage', $e->getMessage());
        }

        return $response
            ->withHeader('Location', "/admin/budget")
            ->withStatus(302);
    }

    public function addIncome($request, $response, $args)
    {

        $twig = Twig::fromRequest($request);

        $content = $request->getParsedBody();

        $id = $args['id'];

        $fund = $this->fundService->findById($id);
        $source = $this->fundSourceService->findById($content['source']);

        try {

            if (!isset($fund)) {
                throw new Exception('Unable To Find Fund with ID of ' . $id);
            }

            if (!isset($source)) {
                throw new Exception('Unable To Find Source with ID of ' . $content['source']);
            }

            $income = new IncomeModel();
            $income->setTitle($content['title']);
            $income->setAmount($content['amount']);
            $income->setFund($fund);
            $income->setSource($source);

            $this->incomeService->save($income);

            return $response
                ->withHeader('Location', "/admin/fund/$id")
                ->withStatus(302);

        } catch (Exception $e) {
            $this->flashMessages->addMessage('errorMessage', $e->getMessage());
        }

        return $response
            ->withHeader('Location', "/admin/budget")
            ->withStatus(302);
    }

    public function addBill($request, $response, $args)
    {

        $twig = Twig::fromRequest($request);

        $content = $request->getParsedBody();

        $fund = $this->fundService->findById($content['fund']);

        try {

            if (!isset($fund)) {
                throw new Exception('Unable To Find Fund');
            }

            $expense = new ExpenseModel();
            $expense->setTitle($content['title']);
            $expense->setFund($fund);
            $expense->setAmount($content['amount']);
            $expense->setPurpose($content['purpose']);
            $expense->setStatus(BudgetStatus::approved());

            $this->expenseService->save($expense);

            $bill = new BillModel();
            $bill->setExpense($expense);
            $this->billService->save($bill);

            $this->flashMessages->addMessage('successMessage', $bill->getExpense()->getTitle() . ' is added');


        } catch (Exception $e) {
            $this->flashMessages->addMessage('errorMessage', $e->getMessage());
        }

        return $response
            ->withHeader('Location', "/admin/budget")
            ->withStatus(302);
    }

    public function addExpense($request, $response, $args)
    {

        $twig = Twig::fromRequest($request);

        $content = $request->getParsedBody();

        $id = $args['id'];

        $fund = $this->fundService->findById($id);

        try {

            if (!isset($fund)) {
                throw new Exception('Unable To Find Fund with ID of ' . $id);
            }

            $expense = new ExpenseModel();
            $expense->setTitle($content['title']);
            $expense->setFund($fund);
            $expense->setAmount($content['amount']);
            $expense->setPurpose($content['purpose']);

            $this->expenseService->save($expense);

            return $response
                ->withHeader('Location', "/admin/fund/$id")
                ->withStatus(302);

        } catch (Exception $e) {
            $this->flashMessages->addMessage('errorMessage', $e->getMessage());
        }

        return $response
            ->withHeader('Location', "/admin/budget")
            ->withStatus(302);
    }

    public function approveExpense($request, $response, $args)
    {

        $content = $request->getParsedBody();

        $id = $args['id'];

        $expense = $this->expenseService->findById($id);

        try {

            if (!isset($expense)) {
                throw new Exception('Unable To Find Expense with ID of ' . $id);
            }

            $expense->setStatus(BudgetStatus::approved());

            $this->expenseService->save($expense);

            return $response
                ->withHeader('Location', "/admin/fund/" . $expense->getFund()->getId())
                ->withStatus(302);

        } catch (Exception $e) {
            $this->flashMessages->addMessage('errorMessage', $e->getMessage());
        }

        return $response
            ->withHeader('Location', "/admin/budget")
            ->withStatus(302);
    }

    public function approveBillExpense($request, $response, $args)
    {

        $id = $args['id'];

        $expense = $this->expenseService->findById($id);

        try {

            if (!isset($expense)) {
                throw new Exception('Unable To Find Expense with ID of ' . $id);
            }

            $expense->setStatus(BudgetStatus::approved());

            $this->expenseService->save($expense);

        } catch (Exception $e) {
            $this->flashMessages->addMessage('errorMessage', $e->getMessage());
        }

        return $response
            ->withHeader('Location', "/admin/budget")
            ->withStatus(302);
    }

    public function rejectBillExpense($request, $response, $args)
    {

        $id = $args['id'];

        $expense = $this->expenseService->findById($id);

        try {

            if (!isset($expense)) {
                throw new Exception('Unable To Find Expense with ID of ' . $id);
            }

            $expense->setStatus(BudgetStatus::rejected());

            $this->expenseService->save($expense);

        } catch (Exception $e) {
            $this->flashMessages->addMessage('errorMessage', $e->getMessage());
        }

        return $response
            ->withHeader('Location', "/admin/budget")
            ->withStatus(302);
    }

    public function rejectExpense($request, $response, $args)
    {

        $content = $request->getParsedBody();

        $id = $args['id'];

        $expense = $this->expenseService->findById($id);

        try {

            if (!isset($expense)) {
                throw new Exception('Unable To Find Expense with ID of ' . $id);
            }

            $expense->setStatus(BudgetStatus::rejected());

            $this->expenseService->save($expense);

            return $response
                ->withHeader('Location', "/admin/fund/" . $expense->getFund()->getId())
                ->withStatus(302);

        } catch (Exception $e) {
            $this->flashMessages->addMessage('errorMessage', $e->getMessage());
        }

        return $response
            ->withHeader('Location', "/admin/budget")
            ->withStatus(302);
    }


    public function budgetManagement($request, $response, $args)
    {

        $twig = Twig::fromRequest($request);

        $content = $request->getParsedBody();
        $query = $request->getQueryParams();

        $archived = false;
        $archiveBill = false;

        if (isset($query['status'])) {
            $archived = !($query['status'] == 'active');
        }

        if (isset($query['bill'])) {
            $archiveBill = !($query['bill'] == 'active');
        }


        $funds = $this->fundService->getAll($archived);

        $tally = $this->fundService->getMonthlyTally(1, 2023);

        $bills = $this->billService->getAll($archiveBill);

        $keys = array_keys($tally);
        $values = array_values($tally);

        return $twig->render($response, 'admin/pages/budget.html', [
            'funds' => $funds,
            'keys' => $keys,
            'values' => $values,
            'bills' => $bills,
            'archived' => $archived,
            'archiveBill' => $archiveBill,
        ]);

    }

    public function overview($request, $response, $args)
    {

        $twig = Twig::fromRequest($request);

        $overview = $this->overviewService->getOverview();

        $staffs = $this->overviewService->getAllStaff();

        $orgStaff = [];

        foreach ($staffs as $staff) {

            $img = $staff->getImg();
            $name = $staff->getName();
            $position = $staff->getPosition();

            $superior = $staff->getSuperior();
            $superiorName = '';

            if (isset($superior)) {
                $superiorName = $superior->getName();
            }

            $orgStaff[] = [
                    'name' => $staff->getName(),
                    'position' => $position,
                    'img' => $img,
                    'superior' => $superiorName,
            ];

        }

//        $payload = json_encode($orgStaff);

        return $twig->render($response, 'admin/pages/overview.html', [
            "overview" => $overview,
            "staffs" => $staffs,
            "org" => $orgStaff,
        ]);

    }

    public function updateOverview($request, $response, $args)
    {

        $twig = Twig::fromRequest($request);

        $path = './resources/overview/';

        $aboutImage = $_FILES['aboutImage'];
        $heroImage = $_FILES['heroImage'];

        $overview = $this->overviewService->getOverview();

        $content = $request->getParsedBody();

        $overview->setAboutDescription($content['aboutDescription']);
        $overview->setHeroDescription($content['heroDescription']);

        if ($aboutImage['error'] !== UPLOAD_ERR_NO_FILE) {
            $imageName = Image::store($path, $aboutImage);
            $overview->setAboutImg(str_replace('.', '', $path) . $imageName);
        }

        if ($heroImage['error'] !== UPLOAD_ERR_NO_FILE) {
            $imageName = Image::store($path, $heroImage);
            $overview->setAboutImg(str_replace('.', '', $path) . $imageName);
        }

        $this->overviewService->saveOverview($overview);

        return $response
            ->withHeader('Location', "/admin/overview")
            ->withStatus(302);

    }

    public function addStaff($request, $response, $args)
    {

        $path = './resources/staff/';

        $twig = Twig::fromRequest($request);

        $image = $_FILES['image'];

        $content = $request->getParsedBody();

        try{
            $superior = $this->overviewService->getStaffById($content['superior']);

            $staff = new Staff();
            $staff->setName($content['name']);
            $staff->setPosition($content['position']);

            if (isset($superior)) {
                $staff->setSuperior($superior);
            }

            if ($image['error'] !== UPLOAD_ERR_NO_FILE) {
                $imageName = Image::store($path, $image);
                $staff->setImg(str_replace('.', '', $path) . $imageName);
            }

            $this->overviewService->saveStaff($staff);
        }catch (Exception $e){

            $message = $e->getMessage();

            if($e->getCode() == 1062){
                $message = 'Name is already define';
            }

            $this->flashMessages->addMessage('errorMessage',$message);
        }


        return $response
            ->withHeader('Location', "/admin/overview")
            ->withStatus(302);

    }

    public function removeStaff($request, $response, $args)
    {

        $twig = Twig::fromRequest($request);

        $content = $request->getParsedBody();

        try{

            $staff = $this->overviewService->getStaffByName($content['name']);

            if(isset($staff)){
                $this->overviewService->deleteStaff($staff);
            }

        }catch (Exception $e){

            $message = $e->getMessage();

            if($e->getCode() == 1451){
                $message = 'Cannot Remove Staff, please remove lower staff first';
            }

            $this->flashMessages->addMessage('errorMessage',$message);
        }

        return $response
            ->withHeader('Location', "/admin/overview")
            ->withStatus(302);

    }

}