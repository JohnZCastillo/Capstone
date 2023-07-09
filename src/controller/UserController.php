<?php

namespace App\controller;

use App\lib\Time;
use App\model\TransactionModel;
use App\model\UserModel;
use App\service\UserService;
use App\service\TransactionService;
use Exception;
use Slim\Psr7\Response;
use Slim\Views\Twig;
use UMA\DIC\Container;

class UserController {

    private UserService $userSerivce;
    private TransactionService $transactionService;

    public function __construct(Container  $container) {
        //get the userService from dependency container
        $this->userSerivce = $container->get(UserService::class);
        $this->transactionService = $container->get(TransactionService::class);
    }

    public function home($request, $response, $args) {

        $view = Twig::fromRequest($request);

        $user = $this->userSerivce->findById(1);;

        $data = [
            'currentMonth' => "June",
            'nextMonth' => "July",
            "currentDue" => "100",
            "nextDue" => "100",
            "unpaid" => "100",
            'transactions' => $user->getTransactions()->toArray()
        ];

        return $view->render($response, 'pages/user-home.html', $data);
    }

    public function pay($request, $response, $args) {

        $view = Twig::fromRequest($request);

        $transaction = new TransactionModel();

        $transaction->setAmount($request->getParsedBody()['amount']);
        $transaction->setForMonth(Time::date($request->getParsedBody()['startDate']));
        $transaction->setToMonth(Time::date($request->getParsedBody()['startDate']));
        $transaction->setCreatedAt(Time::timestamp());
        $transaction->setReceiptId('234');
        $transaction->setUser($this->userSerivce->findById(1));

        $this->transactionService->save($transaction);
        
        return $response
        ->withHeader('Location', '/home')
        ->withStatus(302);
    }

    public function test($request, $response, $args){

        $user =  $this->userSerivce->findById(1);

        var_dump($user->getTransactions());

        return $response;
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
