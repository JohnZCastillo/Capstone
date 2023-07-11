<?php

namespace App\controller;

use App\Lib\Currency;
use App\Lib\Image;
use App\lib\Time;
use App\model\TransactionModel;
use Slim\Views\Twig;

class UserController extends Controller{

    public function home($request, $response, $args) {

        // login in user: PLEASE UPDATE THIS
        $user = $this->getLogin();

        // get the query params
        $queryParams = $request->getQueryParams();

        // if page is present then set value to page otherwise to 1
        $page = isset($queryParams['page']) ? $queryParams['page'] : 1;

        $query = isset($queryParams['query']) ? $queryParams['query'] : null;

        // max transaction per page
        $max = 5;

        $view = Twig::fromRequest($request);

        //Get Transaction
        $result = $this->transactionService->findAll($user, $page, $max, $query);

        $transactions = $result['transactions'];

        $currentMonth = Time::thisMonth();
        $nextMonth = Time::nextMonth();

        $currentDue = $this->transactionService->getBalance($user, $currentMonth, $this->duesService);
        $nextDue = $this->transactionService->getBalance($user, $nextMonth, $this->duesService);

        $paymentSettings = $this->paymentService->findById(1);

        $unpaid = $this->transactionService->getUnpaid($user, $this->duesService, $paymentSettings);

        $data = [
            'currentMonth' => $currentMonth,
            'nextMonth' => $nextMonth,
            "currentDue" => Currency::format($currentDue),
            "nextDue" =>  Currency::format($nextDue),
            "unpaid" =>  Currency::format($unpaid['total']),
            'transactions' => $transactions,
            'totalTransaction' => $result['totalTransaction'],
            'transactionPerPage' => $max,
            'currentPage' => $page,
            'query' => $query,
            'totalPages' => ceil(($result['totalTransaction']) / $max),
            'settings' => $paymentSettings,
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
        $transaction->setToMonth(Time::endMonth($request->getParsedBody()['startDate']));
        $transaction->setCreatedAt(Time::timestamp());

        // set user id to the current login user
        $transaction->setUser($user);

        // gcash receipts sent by user | multiple files
        $images = $_FILES['receipts'];

        // upload path
        $path = './uploads/';

        // save transaction
        $this->transactionService->save($transaction);

        // store physicaly
        $storedImages = Image::storeAll($path, $images);

        // save image to database
        $this->receiptService->saveAll($storedImages, $transaction);

        return $response
            ->withHeader('Location', '/home')
            ->withStatus(302);
    }

    public function test($request, $response, $args) {

        var_dump($this->duesService->getDue('2023-12-01'));
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
        $paymentSettings = $this->paymentService->findById(1);

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
}
