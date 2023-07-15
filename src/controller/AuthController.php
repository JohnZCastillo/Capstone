<?php

namespace App\controller;

use App\lib\Login;
use App\model\enum\UserRole;
use App\model\UserModel;
use App\service\DuesService;
use App\service\PaymentService;
use App\service\ReceiptService;
use App\service\UserService;
use App\service\TransactionService;
use App\service\TransactionLogsService;
use Doctrine\ORM\NoResultException;
use Exception;
use Slim\Views\Twig;
use UMA\DIC\Container;

class AuthController extends Controller{

    public function login($request, $response, $args) {

        try {
            
            $email = $request->getParsedBody()['email'];
            $password = $request->getParsedBody()['password'];

            $user = $this->userSerivce->getUser($email, $password);

            if ($user == null) {
                throw new Exception("Incorrect Email or Password");
            }

            Login::login($user->getId());

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
        $user->setRole(UserRole::user());

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
