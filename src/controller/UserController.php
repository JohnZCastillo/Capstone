<?php

namespace App\controller;

use App\lib\Currency;
use App\lib\Filter;
use App\lib\Helper;
use App\lib\Image;
use App\lib\Login;
use App\lib\Time;
use App\model\enum\AnnouncementStatus;
use App\model\enum\IssuesStatus;
use App\model\IssuesModel;
use App\model\TransactionModel;
use Exception;
use Slim\Views\Twig;

class UserController extends Controller {

    public function home($request, $response, $args) {

        $view = Twig::fromRequest($request);
        $queryParams = $request->getQueryParams();

        $filter = Filter::check($queryParams);

        // Get the user
        $user = $this->getLogin();

        // Get page and set default value to 1 if not provided
        $page = Helper::getArrayValue($queryParams, 'page', 1);

        // Get search query
        $query = Helper::getArrayValue($queryParams, 'query');

        // Set max transactions per page
        $max = 5;

        // Get transactions
        $result = $this->transactionService->getAll($page, $max, $query, $filter, $user);

        // Get balances
        $currentMonth = Time::thisMonth();
        $nextMonth = Time::nextMonth();
        $currentDue = $this->getBalance($currentMonth);
        $nextDue = $this->getBalance($nextMonth);

        // Calculate total dues
        $totalDues = $this->getTotalDues();

        // Prepare data for the view
        $data = [
            'currentMonth' => $currentMonth,
            'nextMonth' => $nextMonth,
            'currentDue' => Currency::format($currentDue),
            'nextDue' => Currency::format($nextDue),
            'unpaid' => Currency::format($totalDues),
            'transactions' => $result['transactions'],
            'totalTransaction' => $result['totalTransaction'],
            'transactionPerPage' => $max,
            'currentPage' => $page,
            'query' => $query,
            'from' => Time::toMonth($filter['from']),
            'to' =>  Time::toMonth($filter['to']),
            'status' =>  $filter['status'],
            'totalPages' => ceil($result['totalTransaction'] / $max),
            'settings' => $this->getPaymentSettings(),
        ];

        return $view->render($response, 'pages/user-home.html', $data);
    }

    /**
     *  Save user transaction on database
     */
    public function pay($request, $response, $args) {

        $user = $this->getLogin();

        $view = Twig::fromRequest($request);

        $transaction = new TransactionModel();

        $transaction->setAmount($request->getParsedBody()['amount']);
        $transaction->setFromMonth(Time::startMonth($request->getParsedBody()['startDate']));
        $transaction->setToMonth(Time::endMonth($request->getParsedBody()['endDate']));
        $transaction->setCreatedAt(Time::timestamp());

        // set user id to the current login user
        $transaction->setUser($user);

        // save transaction
        $this->transactionService->save($transaction);

        // gcash receipts sent by user | multiple files
        $images = $_FILES['receipts'];

        // upload path
        $path = './uploads/';

        // store physicaly
        $storedImages = Image::storeAll($path, $images);

        // save image to database
        $this->receiptService->saveAll($storedImages, $transaction);

        return $response
            ->withHeader('Location', '/home')
            ->withStatus(302);
    }

    public function test($request, $response, $args) {

        var_dump(Login::isLogin());
        // var_dump($this->duesService->getDue('2023-12-01'));
        // var_dump($this->transactionService->findById(1)->getFromMonth());
        // var_dump($this->transactionService->isPaid('2023-01-03'));

        return $response;
    }

    /**
     * View unpaid monthly dues and its total.
     */
    public function dues($request, $response, $args) {

        $view = Twig::fromRequest($request);

        // login in user
        $user = $this->getLogin();

        // Default payment settings is 1
        $paymentSettings = $this->getPaymentSettings();

        //get arrays of unpaid monhts
        $data = $this->transactionService->getUnpaid($user, $this->duesService, $paymentSettings);

        //since the dues in unpaid month are float. 
        //then format it to have peso value / curreny
        $items = Currency::formatArray($data['items'], 'due');

        return $view->render($response, 'pages/dues-breakdown.html', [
            'items' => $items,
            'total' =>  Currency::format($data['total'])
        ]);
    }


    /**
     * This function retrieves a transaction from the database
     * using the provided ID and displays it to the user.
     *
     * @return The rendered HTML page displaying the transaction.
     */
    public function transaction($request, $response, $args) {

        $view = Twig::fromRequest($request);

        // login in user
        $user = $this->getLogin();

        //get transction from databse base on ID
        $transaction = $this->transactionService->findById($args['id']);

        //Default Payment Settings
        $paymentSettings = $this->paymentService->findById(1);

        //Transactions logs
        $logs = $transaction->getLogs();

        return $view->render($response, 'pages/user-transaction.html', [
            'transaction' => $transaction,
            'receipts' => $transaction->getReceipts(),
            'logs' => $logs,
        ]);
    }


    /**
     * View unpaid monthly dues and its total.
     */
    public function announcements($request, $response, $args) {

        $view = Twig::fromRequest($request);

        $queryParams = $request->getQueryParams();

        // if page is present then set value to page otherwise to 1
        $page = isset($queryParams['page']) ? $queryParams['page'] : 1;

        // max transaction per page
        $max = 5;

        $filter = Filter::check($queryParams);

        $result = $this->announcementService->getAll($page, $max, null, $filter);

        return $view->render($response, 'pages/user-announcement.html', [
            'announcements' => $result['announcements'],
            'currentPage' => $page,
            'from' =>  isset($queryParams['from']) ? $queryParams['from'] : null,
            'to' => isset($queryParams['to']) ? $queryParams['to'] : null,
            'status' =>  isset($queryParams['status']) ? $queryParams['status'] : null,
            'totalPages' => ceil(($result['totalAnnouncement']) / $max),
        ]);
    }

    /**
     * View unpaid monthly dues and its total.
     */
    public function accountSettings($request, $response, $args) {

        $user = $this->getLogin();
        $name = $user->getName();
        $email = $user->getEmail();
        $block = $user->getBlock();
        $lot = $user->getLot();

        $view = Twig::fromRequest($request);

        return $view->render($response, 'pages/user-account-settings.html', [
            "name" => $name,
            "email" => $email,
            "block" => $block,
            "lot" => $lot,
        ]);
    }


    /**
     * View Issues.
     */
    public function issues($request, $response, $args) {

        $message = $this->flashMessages->getFirstMessage('message');

        $view = Twig::fromRequest($request);

        $queryParams = $request->getQueryParams();

        // if page is present then set value to page otherwise to 1
        $page = isset($queryParams['page']) ? $queryParams['page'] : 1;

        $type = isset($queryParams['type']) ? $queryParams['type'] : 'posted';

        // max transaction per page
        $max = 5;

        $filter = Filter::check($queryParams);

        $user = $this->getLogin();

        $result = $this->issuesService->getAll($page, $max, null, $filter, $user, $type);

        return $view->render($response, 'pages/user-all-issues.html', [
            'type' => $type,
            'message' => $message,
            'issues' => $result['issues'],
            'currentPage' => $page,
            'from' =>  isset($queryParams['from']) ? $queryParams['from'] : null,
            'to' => isset($queryParams['to']) ? $queryParams['to'] : null,
            'status' =>  isset($queryParams['status']) ? $queryParams['status'] : null,
            'totalPages' => ceil(($result['totalIssues']) / $max),
        ]);
    }

    /**
     * Create an issues
     */
    public function issue($request, $response, $args) {

        $view = Twig::fromRequest($request);

        $queryParams = $request->getQueryParams();

        $issue = new IssuesModel();

        $anonymous = $request->getParsedBody()['anonymous'];

        $issue->setTitle($request->getParsedBody()['title']);
        $issue->setContent($request->getParsedBody()['content']);
        $issue->setCreatedAt(Time::timestamp());
        $issue->setStatus(IssuesStatus::pending());
        $issue->setAction('None');
        $issue->setUser($this->getLogin());
        $issue->setType('posted');

        if ($anonymous) {
            $issue->setUser(null);
            $this->flashMessages->addMessage('message', "Anonymous  Issues Sent");
        }

        $this->issuesService->save($issue);

        return $response
            ->withHeader('Location', "/issues")
            ->withStatus(302);
    }

    public function archiveIssue($request, $response, $args) {

        $view = Twig::fromRequest($request);

        $id = $args['id'];

        $issue = $this->issuesService->findById($id);

        $issue->setType('archive');

        $this->issuesService->save($issue);

        return $response
            ->withHeader('Location', "/issues")
            ->withStatus(302);
    }

    public function unArchiveIssue($request, $response, $args) {

        $view = Twig::fromRequest($request);

        $id = $args['id'];

        $issue = $this->issuesService->findById($id);

        $issue->setType('posted');

        $this->issuesService->save($issue);

        return $response
            ->withHeader('Location', "/issues")
            ->withStatus(302);
    }
}
