<?php

namespace App\controller;

use App\lib\Login;
use App\lib\LoginDetails;
use App\model\enum\UserRole;
use App\model\LoginHistoryModel;
use App\model\UserModel;
use App\service\DuesService;
use App\service\PaymentService;
use App\service\ReceiptService;
use App\service\UserService;
use App\service\TransactionService;
use App\service\TransactionLogsService;
use Doctrine\ORM\NoResultException;
use Exception;
use Respect\Validation\Validator as V;
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

            $loginHistoryModel = new LoginHistoryModel();
            $loginDetails = LoginDetails::getLoginDetails();

            $loginHistoryModel->setLoginDate($loginDetails['loginTime']);
            $loginHistoryModel->setIp($loginDetails['ipAddress']);
            $loginHistoryModel->setDevice($loginDetails['deviceLogin']);
            $loginHistoryModel->setSession($loginDetails['sessionId']);
            $loginHistoryModel->setUser($user);

            $this->loginHistoryService->addLoginLog($loginHistoryModel);

            return $response
                ->withHeader('Location', "/home")
                ->withStatus(302);
        } catch (Exception $ex) {
            
            $view = Twig::fromRequest($request);

            return $view->render($response, 'pages/login.html', [
                'loginErrorMessage' => $ex->getMessage(),
            ]);

        }
        
    }

    public function logout($request, $response, $args) {
            $this->loginHistoryService->addLogoutLog();

        session_regenerate_id();

        session_destroy();


            return $response
                ->withHeader('Location', "/login")
                ->withStatus(302);

    }

    /**
     * 
     * Register new User to database.
     */
    public function register($request, $response, $args) {

        $view = Twig::fromRequest($request);

        // Creat user model
        $user = new UserModel();

        $content = $request->getParsedBody();

        // update user information from post request parameters
        $user->setName($content['name']);
        $user->setEmail($content['email']);
        $user->setPassword($content['password']);
        $user->setBlock($content['block']);
        $user->setLot($content['lot']);
        $user->setRole(UserRole::user());

        try {

            $userValidator = V::attribute('name',V::stringType()->length(1, 32))
                            ->attribute('email',V::email());


            if(!V::stringType()->length(2,30)->validate($user->getName())){
                $data['nameError'] = "Name lenght must be within 3 - 30";
                throw new Exception('');
            }

            if(!V::email()->validate($user->getEmail())){
                $data['emailError'] = "not an email";
                throw new Exception('');
            }

            $this->userSerivce->save($user);
            return $view->render($response, 'pages/register.html', []);
        } catch (Exception $e) {

            $data['error'] = "Something Went Wrong";

            //error code for duplicate entry
            if ($e->getCode() == 1062) {
                $data['error'] = "Email Is Already In Used";
            }

            $response->withStatus(500);
            return $view->render($response, 'pages/register.html', $data);
        }
    }
}
