<?php

namespace App\controller;

use App\lib\Filter;
use App\lib\Helper;
use App\lib\Image;
use App\lib\ReportMaker;
use App\lib\Time;
use App\model\AnnouncementModel;
use App\model\enum\AnnouncementStatus;
use App\model\enum\UserRole;
use App\model\LogsModel;
use App\model\PaymentModel;
use App\model\PrivilegesModel;
use App\model\TransactionModel;
use App\model\UserModel;
use DateTime;
use Exception;
use Respect\Validation\Validator as V;
use Slim\Views\Twig;
use TCPDF;

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

        return $view->render($response, 'pages/admin-home.html', $data);
    }

    /**
     *   Get Transaction Base on id
     */
    public function transaction($request, $response, $args)
    {

        $view = Twig::fromRequest($request);

        $transaction = $this->transactionService->findById($args['id']);

        $user = $transaction->getUser();

        return $view->render($response, 'pages/admin-transaction.html', [
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

        return $view->render($response, 'pages/admin-announcement.html', [
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


    public function manualPayment($request, $response, $args)
    {

        try {


            $content = $request->getParsedBody();

            $user = $this->userSerivce->findByEmail($content['block'] . $content['block'] . "@manual.payment");

            //only create new user when manual payment user has not been created
            if ($user == null) {

                $user = new UserModel();

                // update user information from post request parameters
                $user->setName("manual payment")
                    ->setBlock($content['block'])
                    ->setLot($content['lot'])
                    ->setRole(UserRole::user())
                    ->setPassword("")
                    ->setEmail($content['block'] . $content['block'] . "@manual.payment")
                    ->setIsBlocked(false);

                $this->userSerivce->save($user);

                //create privileges
                $privileges = new PrivilegesModel();
                $privileges->setUserAnnouncement(true)
                    ->setUserIssues(true)
                    ->setUserPayment(true)
                    ->setAdminIssues(false)
                    ->setAdminPayment(false)
                    ->setAdminAnnouncement(false)
                    ->setAdminUser(false);

                $privileges->setUser($user);
                $this->priviligesService->save($privileges);
            }


            $fromMonth = $content['from'];
            $fromMonth = Time::setToFirstDayOfMonth($fromMonth);

            $toMonth = $content['to'];
            $toMonth = Time::setToLastDayOfMonth($toMonth);

            $fromMonth2 = Time::nowStartMonth($content['from']);
            $toMonth2 = Time::nowStartMonth($content['to']);

            $months = Time::getMonths($fromMonth2, $toMonth2);

            foreach ($months as $month) {
                if ($this->transactionService->isPaid($user, $month)) {
                    throw new Exception("Monthly due was already paid for this property for month "
                        . $fromMonth2 . " - " . $toMonth2);
                }
            }

            $transaction = new TransactionModel();
            $transaction->setAmount($content["amount"]);
            $transaction->setFromMonth(Time::convertDateStringToDateTime($fromMonth));
            $transaction->setToMonth(Time::convertDateStringToDateTime($toMonth));
            $transaction->setCreatedAt(Time::timestamp());
            $transaction->setUser($user);

            $message = "Payment was approved";

            //login admin who approved the payment
            $admin = $this->getLogin();

            //set transaction
            $transaction->setStatus('APPROVED');

            // save transaction
            $this->transactionService->save($transaction);

            //save logs
            $this->logsService->log($transaction, $admin, $message, 'APPROVED');


            $action = "Manual payment for transaction with id of " . $transaction->getId() . " was created";

            $actionLog = new LogsModel();
            $actionLog->setAction($action);
            $actionLog->setTag("Manual Payment");
            $actionLog->setUser($this->getLogin());
            $actionLog->setCreatedAt(new DateTime());
            $this->actionLogs->addLog($actionLog);


            // Create a new TCPDF instance
            $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

            // Do not print the header line
            $pdf->SetPrintHeader(false);

            // Add a page
            $pdf->AddPage();

            $pdf->SetFont('times', 'B', 16);

            $pdf->Image('./resources/logo.jpeg', 10, 10, 20);
            $pdf->Cell(0, 10, 'Carissa Homes Subdivision Phase 7', 0, 1, 'C', false); // Add 'false' for no border
            $pdf->Cell(0, 10, 'Monthly Dues Invoice', 0, 1, 'C', false); // Add 'false

            $pdf->SetFont('times', '', 12);

            $transactionNumber = 'TRX123456';
            $homeownerName = 'John Doe';
            $amount = '150.00';
            $paymentDate = 'August 1, 2023';
            $coverage = 'July 2023 - August 2023';

            $pdf->Cell(0, 10, 'Transaction Number: ' . $transactionNumber, 0, 1);
            $pdf->Cell(0, 10, 'Homeowner: ' . $homeownerName, 0, 1);
            $pdf->Cell(0, 10, 'Amount: ' . $amount, 0, 1);
            $pdf->Cell(0, 10, 'Payment Date: ' . $paymentDate, 0, 1);
            $pdf->Cell(0, 10, 'Coverage: ' . $coverage, 0, 1);

            $pdf->Ln(10); // Add some vertical spacing
            $pdf->MultiCell(0, 10, 'This invoice serves as proof that the payment has been made.', 0, 'L');

            $pdfContent = $pdf->Output('', 'S');

            header('Content-Type: application/pdf');
            header('Content-Disposition: attachment; filename="monthly_dues_receipt.pdf"');
            header('Content-Length: ' . strlen($pdfContent));

            echo $pdfContent;

        } catch (Exception $e) {

            $this->flashMessages->addMessage("errorMessage", $e->getMessage());

            return $response
                ->withHeader('Location', "/admin/home")
                ->withStatus(302);
        }

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

        return $view->render($response, 'pages/admin-all-announcement.html', [
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

        return $view->render($response, 'pages/admin-all-issues.html', [
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

        return $view->render($response, 'pages/admin-all-users.html', [
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

        return $view->render($response, 'pages/admin-manage-issue.html', [
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

        $loginHistory = $this->loginHistoryService->getLogs($user);
        $currentSession = session_id();

        $view = Twig::fromRequest($request);

        return $view->render($response, 'pages/admin-account-settings.html', [
            "loginHistory" => $loginHistory,
            "sessionId" => $currentSession,
            'loginUser' => $this->getLogin(),
            "user" => $user,
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

        return $twig->render($response, 'pages/admin-system-settings.html', [
            'timezone' => $timezone,
            "loginUser" => $user,
            "systemSettings" => $systemSettings,
        ]);

    }

    public function announcementPage($request, $response, $args)
    {

        $twig = Twig::fromRequest($request);

        $user = $this->getLogin();

        return $twig->render($response, 'pages/admin-announcement.html', [
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

        return $twig->render($response, 'pages/admin-all-logs.html', $data);
    }

    public function test($request, $response, $args)
    {
        $twig = Twig::fromRequest($request);

        $total = $this->transactionService->getTotal("APPROVED", "2023-01-01", "2023-01-31");
        var_dump($total);
    }

    public function report($request, $response, $args)
    {

        $params = $request->getParsedBody();
        $fromMonth = $params['from'];
        $toMonth = $params['to'];

        $block = $params['block'];
        $lot = $params['lot'];

        $status = $params['reportStatus'];

        $fromMonth = Time::setToFirstDayOfMonth($fromMonth);
        $toMonth = Time::setToLastDayOfMonth($toMonth);

        $action = "User with id of " . $this->getLogin()->getId() . " generated a report";

        $actionLog = new LogsModel();
        $actionLog->setAction($action);
        $actionLog->setTag("Admin");
        $actionLog->setUser($this->getLogin());
        $actionLog->setCreatedAt(new DateTime());
        $this->actionLogs->addLog($actionLog);

        // Create a new TCPDF instance
        $pdf = new TCPDF('L', PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
        // Do not print the header line
        $pdf->SetPrintHeader(false);

        // Add a page
        $pdf->AddPage();

        // Set font for title and headings
        $pdf->SetFont('helvetica', 'B', 24);
        $pdf->SetTextColor(0, 51, 102); // Dark blue color
        $pdf->Image('./resources/logo.jpeg', 10, 10, 30);
        $pdf->Cell(0, 20, 'Carissa Homes Subdivision Phase 7', 0, 1, 'C');

        // Set font for report details
        $pdf->SetFont('helvetica', 'B', 14);
        $pdf->SetTextColor(0); // Reset text color to black

        // Display coverage date
        $dateCoverage = 'Coverage Period: ' . Time::toStringMonthYear($fromMonth) . " - " . Time::toStringMonthYear($toMonth);

        $pdf->Cell(0, 10, $dateCoverage, 0, 1, 'C');

        // Display report generation date
        $generationDate = 'Report Generated On: ' . date('F j, Y, g:i a'); // Current date and time
        $pdf->Cell(0, 10, $generationDate, 0, 1, 'C');

        // Display administrator information
        $generatedBy = 'Generated by: ' . $this->getLogin()->getName();
        $pdf->Cell(0, 10, $generatedBy, 0, 1, 'C');

        $pdf->Ln(10); // Add some vertical space

        $totalCollection = $this->transactionService->getTotal("APPROVED", $fromMonth, $toMonth);
        $results = $this->transactionService->getApprovedPayments($fromMonth, $toMonth, $status);

        $content = [
            array("Transaction No.", "Name", "Unit", "Amount", "Approved By", "Receipt Ref.", "Payment Coverage", "Payment Date"),
        ];

        foreach ($results as $result) {

            $receipts = $result->getReceipts();

            $references = "";

            $fromMonthCoverage = Time::toStringMonthYear($result->getFromMonth());
            $toMonthCoverage = Time::toStringMonthYear($result->getToMonth());

            $paymentCoverage = $fromMonthCoverage . " - " . $toMonthCoverage;

            foreach ($receipts as $receipt) {
                $references = $references . $receipt->getReferenceNumber() . "\n";
            }

            $content[] = [
                $result->getId(),
                $result->getUser()->getName(),
                "B" . $result->getUser()->getBlock() . " L" . $result->getUser()->getLot(),
                $result->getAmount(),
                $result->getLogs()[0]->getUpdatedBy()->getName(),
                $references,
                $paymentCoverage,
                Time::convertDateTimeToDateString($result->getCreatedAt())

            ];
        }

        $report_data = array(
            "Financial Overview" => array(
                "Total Collected Dues: $totalCollection",
            ),
            "Approved Transaction Breakdown" => $content
        );

        foreach ($report_data as $section_title => $section_content) {
            $pdf->SetFont('helvetica', 'B', 16);
            $pdf->SetFillColor(230, 230, 230); // Light gray background for section title
            $pdf->Cell(0, 10, $section_title, 1, 1, 'L', 1, '', true);

            if ($section_title === "Approved Transaction Breakdown") {
                $pdf->SetFont('helvetica', '', 12);
                $colWidths = array(37, 40, 25, 25, 40, 30, 40, 40); // Adjusted column widths
                // Add header row
                for ($i = 0; $i < count($section_content[0]); $i++) {
                    $pdf->Cell($colWidths[$i], 10, $section_content[0][$i], 1, 0, 'C', 1);
                }
                $pdf->Ln(); // Move to the next row

                // Add data rows
                for ($rowIdx = 1; $rowIdx < count($section_content); $rowIdx++) {
                    for ($colIdx = 0; $colIdx < count($section_content[$rowIdx]); $colIdx++) {
                        $pdf->Cell($colWidths[$colIdx], 10, $section_content[$rowIdx][$colIdx], 1, 0, 'C');
                    }
                    $pdf->Ln(); // Move to the next row
                }

            } else {
                foreach ($section_content as $line) {
                    $pdf->SetFont('helvetica', '', 12);
                    $pdf->MultiCell(0, 10, $line, 0, 'L');
                }
            }

            // Add some space between sections
            $pdf->Ln(10);
        }


        $pdfContent = $pdf->Output('', 'S');

        $response->getBody()->write($pdf->Output('', 'S'));

        return $response
            ->withHeader('Content-Type', 'application/pdf')
            ->withHeader('Content-Disposition', 'inline; filename="filename.pdf"');

    }

    public function reportUnpaid($request, $response, $args)
    {

        $params = $request->getParsedBody();
        $fromMonth = $params['from'];
        $toMonth = $params['to'];

        $block = $params['block'] == "ALL" ? null : $params['block'];
        $lot = $params['lot'] == "ALL" ? null : $params['lot'];

        $status = $params['reportStatus'];

        $fromMonth = Time::setToFirstDayOfMonth($fromMonth);
        $toMonth = Time::setToLastDayOfMonth($toMonth);

        $loginUser = $this->getLogin();

        $action = "User with id of " . $loginUser->getId() . " generated a report";

        $this->addActionLog($action);

        $reportMaker = new ReportMaker($loginUser, $fromMonth, $toMonth);

        $users = $this->userSerivce->findUsers($block, $lot);

        $content = array(
            ReportMaker::$UNPAID_HEADER,
        );

        $total = 0;

        foreach ($users as $user) {

            $unpaidData = $this->transactionService->getUnpaid($user,
                $this->duesService,
                $this->getPaymentSettings(),
                Time::setToFirstDayOfMonth($params['from']),
                Time::setToFirstDayOfMonth($params['to']),
            );

            $total = +$unpaidData['total'];

            $unpaids = ReportMaker::unpaid($user, $unpaidData);

            foreach ($unpaids as $unpaid) {
                $content[] = $unpaid;
            }

        }

        $report_data = array(
            "Total Unpaid Due" => [$total],
            "Unpaid Due Breakdown" => $content,
        );

        $reportMaker->addBody($report_data, [100, 50, 50, 77], "Unpaid Due Breakdown");

        $response->getBody()->write($reportMaker->output());

        return $response
            ->withHeader('Content-Type', 'application/pdf')
            ->withHeader('Content-Disposition', 'inline; filename="filename.pdf"');

    }

    public function updateSystemSettings($request, $response, $args)
    {

        $content = $request->getParsedBody();

        $systemSettings = $this->systemSettingService->findById();

        $systemSettings->setAllowSignup($content['allowSignup']);
        $systemSettings->setTermsAndCondition($content['termsAndCondition']);
        $systemSettings->setMailHost($content['mailHost']);
        $systemSettings->setMailUsername($content['mailUsername']);

//        only update password if not empty
        if (!empty($content['mailPassword'])) {
            $systemSettings->setMailPassword($content['mailPassword']);
        }

        $this->systemSettingService->save($systemSettings);

        return $response
            ->withHeader('Location', "/admin/system")
            ->withStatus(302);
    }

}