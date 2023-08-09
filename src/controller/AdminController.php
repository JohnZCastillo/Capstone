<?php

namespace App\controller;

use App\lib\Filter;
use App\lib\Helper;
use App\lib\Image;
use App\lib\Time;
use App\model\AnnouncementModel;
use App\model\enum\AnnouncementStatus;
use App\model\PaymentModel;
use Slim\Views\Twig;
use Exception;

class AdminController extends Controller {

    public function home($request, $response, $args)
    {

        // get the query params
        $queryParams = $request->getQueryParams();

        $settings = $this->paymentService->findById(1);

        // if page is present then set value to page otherwise to 1
        $page = $queryParams['page'] ?? 1;

        $filter = Filter::check($queryParams);

        $id = $queryParams['query'] ?? null;

        // max transaction per page
        $max = 4;

        $view = Twig::fromRequest($request);

        //Get Transaction
        $result = $this->transactionService->getAll($page, $max, $id, $filter);

        try {

            $paymentSettings = $this->getPaymentSettings();

            if($paymentSettings == null){
                throw new Exception("Payment Not Set");
            }

            $startOfPaymentDate = $paymentSettings->getStart();
            $startOfPaymentYear = Time::getYearFromStringDate($startOfPaymentDate);
            $dues = [];

            $datesForMonths = Time::getDatesForMonthsOfYear($startOfPaymentYear);

            foreach ($datesForMonths as $month => $dates) {
                $dues[] = [
                    "date" => $dates,
                    "amount" => $this->duesService->getDue($dates),
                    "savePoint" => $this->duesService->isSavePoint($dates)
                ];
            }

        } catch (Exception $e) {
            var_dump($e->getMessage());
        }


        $data = [
            'paymentStart' => $startOfPaymentYear ?? null,
            'dues' => $dues ?? null,
            'transactions' => $result->getItems(),
            'totalTransaction' => $result->getTotal(),
            'transactionPerPage' => $max,
            'currentPage' => $page,
            'query' => $id,
            'from' => $queryParams['from'] ?? null,
            'to' => $queryParams['to'] ?? null,
            'status' => $queryParams['status'] ?? null,
            'totalPages' => ceil(($result->getTotal()) / $max),
            'settings' => $settings
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

        return $response
            ->withHeader('Location', "/admin")
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

        if (Helper::existAndNotNull($id)) {
            $post = $this->announcementService->findById($id);
        }

        $post->setTitle($title);
        $post->setContent($content);
        $post->setStatus(AnnouncementStatus::posted());

        try {
            $this->announcementService->save($post);
            $this->flashMessages->addMessage('message', 'Announcement ' . $post->getTitle() . 'Posted');
        } catch (\Throwable $th) {
            $this->flashMessages->addMessage('message', 'Announcement ' . $post->getTitle() . 'Posting Error');
        }

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

        $this->flashMessages->addMessage('Test', 'This is a message');

        return $view->render($response, 'pages/admin-announcement.html', [
            'announcement' => $announcement,
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

        return $view->render($response, 'pages/admin-all-announcement.html', [
            'announcements' => $result['announcements'],
            'message' => $message,
            'query' => $id,
            'currentPage' => $page,
            'from' => isset($queryParams['from']) ? $queryParams['from'] : null,
            'to' => isset($queryParams['to']) ? $queryParams['to'] : null,
            'status' => isset($queryParams['status']) ? $queryParams['status'] : null,
            'totalPages' => ceil(($result['totalAnnouncement']) / $max),
            'status' => $status
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

        $createdAt = empty($queryParams['createdAt']) ? null :$queryParams['createdAt'] ;

        $query = empty($queryParams['query']) ? null : $queryParams['query'];

        $pagination = $this->issuesService->getAll($page, $max, $query, $filter, null, $type,$createdAt);

        return $view->render($response, 'pages/admin-all-issues.html', [
            'type' => $type,
            'message' => $message,
            'issues' => $pagination->getItems(),
            'currentPage' => $page,
            'status' => $queryParams['status'] ?? null,
            'paginator' => $pagination,
            'createdAt' => $createdAt
        ]);
    }

    public function users($request, $response, $args)
    {

        $view = Twig::fromRequest($request);

        $queryParams = $request->getQueryParams();

        // if page is present then set value to page otherwise to 1
        $page = $queryParams['page'] ?? 1;

        $role = $queryParams['role'] ?? 'admin';

        // max transaction per page
        $max = 3;

        $filter = Filter::check($queryParams);

        $query = empty($queryParams['query']) ? null : $queryParams['query'];

        $pagination = $this->userSerivce->getAll($page, $max, $query, $filter, $role);

        return $view->render($response, 'pages/admin-all-users.html', [
            'users' => $pagination->getItems(),
            'currentPage' => $page,
            'role' => $role,
            'paginator' => $pagination,
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
        $name = $user->getName();
        $email = $user->getEmail();
        $block = $user->getBlock();
        $lot = $user->getLot();

        $loginHistory = $this->loginHistoryService->getLogs($user);
        $currentSession = session_id();

        $view = Twig::fromRequest($request);

        return $view->render($response, 'pages/admin-account-settings.html', [
            "loginHistory" => $loginHistory,
            "sessionId" => $currentSession,
            "name" => $name,
            "email" => $email,
            "block" => $block,
            "lot" => $lot,
        ]);
    }
}