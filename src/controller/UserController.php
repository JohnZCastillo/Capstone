<?php

namespace App\controller;

use App\Lib\Currency;
use App\lib\Time;
use App\model\TransactionModel;
use App\model\UserModel;
use App\service\DuesService;
use App\service\UserService;
use App\service\TransactionService;
use Exception;
use Slim\Psr7\Response;
use Slim\Views\Twig;
use UMA\DIC\Container;

class UserController {

    private UserService $userSerivce;
    private TransactionService $transactionService;
    private DuesService $duesService;

    public function __construct(Container  $container) {
        //get the userService from dependency container
        $this->userSerivce = $container->get(UserService::class);
        $this->transactionService = $container->get(TransactionService::class);
        $this->duesService = $container->get(DuesService::class);
    }

    public function home($request, $response, $args) {

        // get the query params
        $queryParams = $request->getQueryParams();

        // if page is present then set value to page otherwise to 1
        $page = isset($queryParams['page']) ? $queryParams['page'] : 1;

        $query = isset($queryParams['query']) ? $queryParams['query'] : null;

        // max transaction per page
        $max = 5;

        $view = Twig::fromRequest($request);

        // login in user !Note: PLEASE UPDATE THIS
        $user = $this->userSerivce->findById(1);

        //Get Transaction
        $result = $this->transactionService->findAll($user,$page,$max,$query);
        
        $transactions = $result['transactions'];

        $currentMonth = Time::thisMonth();
        $nextMonth = Time::nextMonth();

        $currentDue = $this->transactionService->getBalance($user,$currentMonth,$this->duesService);
        $nextDue = $this->transactionService->getBalance($user,$nextMonth,$this->duesService);

        $unpaid = $this->transactionService->getUnpaid($user,$this->duesService);

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
        ];

        return $view->render($response, 'pages/user-home.html', $data);
    }

    /**
     *  Save user transaction on database
     */
    public function pay($request, $response, $args) {

        $view = Twig::fromRequest($request);

        $transaction = new TransactionModel();

        $transaction->setAmount($request->getParsedBody()['amount']);
        $transaction->setFromMonth(Time::startMonth($request->getParsedBody()['startDate']));
        $transaction->setToMonth(Time::endMonth($request->getParsedBody()['startDate']));
        $transaction->setCreatedAt(Time::timestamp());
        $transaction->setReceiptId('234');
        $transaction->setUser($this->userSerivce->findById(1));

        //save transaction
        $this->transactionService->save($transaction);
        
        // return response
        return $response
        ->withHeader('Location', '/home')
        ->withStatus(302);
    }

    public function test($request, $response, $args){

        var_dump($this->duesService->getDue('2023-12-01'));
        // var_dump($this->transactionService->findById(1)->getFromMonth());
        // var_dump($this->transactionService->isPaid('2023-01-03'));

        return $response;
    }

    public function dues($request, $response, $args){
        $view = Twig::fromRequest($request);
        
        // login in user !Note: PLEASE UPDATE THIS
        $user = $this->userSerivce->findById(1);

        $data = $this->transactionService->getUnpaid($user,$this->duesService);

        $items = Currency::formatArray($data['items'],'due');

        return $view->render($response, 'pages/dues-breakdown.html', [
            'items' => $items,
            'total' =>  Currency::format($data['total'])
        ]);

    }

    public function transaction($request, $response, $args){
        $view = Twig::fromRequest($request);
        
        // login in user !Note: PLEASE UPDATE THIS
        $user = $this->userSerivce->findById(1);

        $data = $this->transactionService->getUnpaid($user,$this->duesService);

        $items = Currency::formatArray($data['items'],'due');

        return $view->render($response, 'pages/user-transaction.html', [
        ]);

    }

    /**
     * 
     * Register new User to database.
     */
    public function register($request, $response, $args) {

        $view = Twig::fromRequest($request);

        // Creat user model
        $user = new UserModel();

        // update user information from post request parameters
        $user->setName($request->getParsedBody()['name']);
        $user->setEmail($request->getParsedBody()['email']);
        $user->setPassword($request->getParsedBody()['password']);
        $user->setBlock($request->getParsedBody()['block']);
        $user->setLot($request->getParsedBody()['lot']);

        try {
            $this->userSerivce->save($user);
            return $view->render($response, 'pages/user-home.html', []);
        } catch (Exception $e) {

            $data['message'] = "Something Went Wrong";

            //error code for duplicate entry
            if ($e->getCode() == 1062) {
                $data['message'] = "Email Is Already In Used";
            } 

            $response->withStatus(500);
            return $view->render($response, 'pages/register.html', $data);
        }
    }
}
