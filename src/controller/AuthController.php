<?php

namespace App\controller;

use App\Lib\Currency;
use App\Lib\Image;
use App\lib\Login;
use App\lib\Time;
use App\model\PaymentModel;
use App\model\TransactionModel;
use App\model\UserModel;
use App\service\DuesService;
use App\service\PaymentService;
use App\service\ReceiptService;
use App\service\UserService;
use App\service\TransactionService;
use App\service\TransactionLogsService;
use Exception;
use Slim\Psr7\Response;
use Slim\Views\Twig;
use UMA\DIC\Container;

class AuthController {

    private UserService $userSerivce;
    private TransactionService $transactionService;
    private DuesService $duesService;
    private ReceiptService $receiptService;
    private TransactionLogsService $logsService;
    private PaymentService $paymentService;

    public function __construct(Container  $container) {
        //get the userService from dependency container
        $this->userSerivce = $container->get(UserService::class);
        $this->transactionService = $container->get(TransactionService::class);
        $this->duesService = $container->get(DuesService::class);
        $this->receiptService = $container->get(ReceiptService::class);
        $this->logsService = $container->get(TransactionLogsService::class);
        $this->paymentService = $container->get(PaymentService::class);
    }

    public function login($request, $response, $args) {

        try {
            $email = $request->getParsedBody()['email'];
            $password = $request->getParsedBody()['password'];

            $user = $this->userSerivce->getUser($email, $password);

            if ($user == null) {
                throw new Exception("Incorrect Email or Password");
            }

            Login::login($user);

            return $response
                ->withHeader('Location', "/home")
                ->withStatus(302);
        } catch (Exception $ex) {
            
            $view = Twig::fromRequest($request);

            return $view->render($response, 'pages/login.html', [
                'message' => $ex->getMessage(),
            ]);

        }
        
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
            return $view->render($response, 'pages/register.html', []);
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
