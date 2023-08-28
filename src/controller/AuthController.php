<?php

namespace App\controller;

use App\lib\Login;
use App\lib\LoginDetails;
use App\model\enum\UserRole;
use App\model\LoginHistoryModel;
use App\model\PrivilegesModel;
use App\model\UserModel;
use Exception;
use Respect\Validation\Validator as V;
use Slim\Views\Twig;

class AuthController extends Controller {


    private function log()
    {
        $user = $this->getLogin();
        $loginHistoryModel = new LoginHistoryModel();
        $loginDetails = LoginDetails::getLoginDetails();

        $loginHistoryModel->setLoginDate($loginDetails['loginTime']);
        $loginHistoryModel->setIp($loginDetails['ipAddress']);
        $loginHistoryModel->setDevice($loginDetails['deviceLogin']);
        $loginHistoryModel->setSession($loginDetails['sessionId']);
        $loginHistoryModel->setUser($user);

        $this->loginHistoryService->addLoginLog($loginHistoryModel);
    }

    public function login($request, $response, $args)
    {

        try {

            if (!Login::isLogin()) {
                $email = $request->getParsedBody()['email'];
                $password = $request->getParsedBody()['password'];

                $user = $this->userSerivce->getUser($email, $password);

                if ($user == null) {
                    throw new Exception("Incorrect Email or Password");
                }

                Login::login($user->getId());

            }

            $this->log();

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

    public function logout($request, $response, $args)
    {
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
    public function register($request, $response, $args)
    {

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

            $userValidator = V::attribute('name', V::stringType()->length(1, 32))
                ->attribute('email', V::email());


            if (!V::stringType()->length(2, 30)->validate($user->getName())) {
                $data['nameError'] = "Name lenght must be within 3 - 30";
                throw new Exception('');
            }

            if (!V::email()->validate($user->getEmail())) {
                $data['emailError'] = "not an email";
                throw new Exception('');
            }

            $this->userSerivce->save($user);
            $priviliges = new PrivilegesModel();
            $priviliges->setUserAnnouncement(true)
                ->setUserIssues(true)
                ->setUserPayment(true)
                ->setAdminIssues(false)
                ->setAdminPayment(false)
                ->setAdminAnnouncement(false)
                ->setAdminUser(false);

            $priviliges->setUser($user);
            $this->priviligesService->save($priviliges);


            Login::login($user->getId());

            $this->log();

            $name = $this->getLogin()->getName();

            $message = "Maligayang Pagdating sa Carissa Homes Subdivision Portal! $name
  Kasama ka na sa masayang komunidad ng Carissa Homes. Tara, mag-explore tayo!";

            $this->flashMessages->addMessage('welcome', $message);

            return $response
                ->withHeader('Location', "/home")
                ->withStatus(302);

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
