<?php

namespace App\controller;

use App\lib\Login;
use App\lib\LoginDetails;
use App\lib\Randomizer;
use App\lib\Time;
use App\model\enum\UserRole;
use App\model\LoginHistoryModel;
use App\model\PrivilegesModel;
use App\model\UserModel;
use Exception;
use Respect\Validation\Validator as V;
use Slim\Views\Twig;

class AuthController extends Controller
{


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

            $content = $request->getParsedBody();

            // Validation for the "email" field
            if (!V::notEmpty()->validate($content['email'])) {
                throw new Exception('Email is required.');
            }

            // Validation for the "email" field
            if (!V::notEmpty()->validate($content['password'])) {
                throw new Exception('Password is required.');
            }

            if ($content['email'] != "admin@admin") {
                if (!V::email()->validate($content['email'])) {
                    throw new Exception("Please use a valid email");
                }
            }


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

        $content = $request->getParsedBody();

        try {

            if (!V::key("agree")->validate($content)) {
                $data['conditionError'] = "You must agree to our terms and condition to continue";
                throw new Exception('use must agree.');
            }

            if (!V::notEmpty()->validate($content['agree'])) {
                $data['conditionError'] = "You must agree to our terms and condition to continue";
                throw new Exception('use must agree.');
            }

            if (!V::notEmpty()->validate($content['name'])) {
                $data['nameError'] = "Name is required";
                throw new Exception('Name is required.');
            }

            // Validation for the "email" field
            if (!V::notEmpty()->validate($content['email'])) {
                $data['emailError'] = "Email is required";
                throw new Exception('Email is required.');
            }

            // Validation for the "block" field
            if (!V::notEmpty()->validate($content['block'])) {
                $data['blockError'] = "Block is required";
                throw new Exception('Block is required.');
            }

            // Validation for the "lot" field
            if (!V::notEmpty()->validate($content['lot'])) {
                $data['lotError'] = "Lot is required";
                throw new Exception('Lot is required.');
            }

            // Validation for the "password" field
            if (!V::notEmpty()->validate($content['password'])) {
                $data['passwordError'] = "Password is required";
                throw new Exception('Password is required.');
            }

            // Validation for the "password2" field (password confirmation)
            if (!V::notEmpty()->validate($content['password2'])) {
                $data['password2Error'] = "Password confirmation is required";
                throw new Exception('Password confirmation is required.');
            }

            if (!V::equals($content['password'])->validate($content['password2'])) {
                $data['password2Error'] = "Confirm password does not match password";
                throw new Exception('');
            }


            if (!V::stringType()->length(2, 30)->validate($content['name'])) {
                $data['nameError'] = "Name length must be within 3 - 30";
                throw new Exception('');
            }

            if (!V::stringType()->length(8, 30)->validate($content['password'])) {
                $data['passwordError'] = "Password length must be within 8 - 30";
                throw new Exception('');
            }

            if (!V::stringType()->validate($content['block'])) {
                $data['blockError'] = "Block must be an string";
                throw new Exception('');
            }

            if (!V::stringType()->validate($content['lot'])) {
                $data['lotError'] = "lot must be an string";
                throw new Exception('');
            }

            if (!V::email()->validate($content['email'])) {
                $data['emailError'] = "Please use a valid email";
                throw new Exception('');
            }


            // Creat user model
            $user = new UserModel();
            $user->setName($content['name']);
            $user->setEmail($content['email']);
            $user->setPassword($content['password']);
            $user->setBlock($content['block']);
            $user->setLot($content['lot']);
            $user->setRole(UserRole::user());
            $user->setIsBlocked(false);

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

            //error code for duplicate entry
            if ($e->getCode() == 1062) {
                $data['emailError'] = "Email Is Already In Used";
            }

            $data['content'] = $content;

            $response->withStatus(500);
            return $view->render($response, 'pages/register.html', $data);
        }
    }


    public function code($request, $response, $args)
    {
        try {

            $view = Twig::fromRequest($request);


            $content = $request->getParsedBody();

            $session = session_id();
            $code = $content['code'];
            $currentTime = new \DateTime();

            $valid = $this->codeModelService->isValid($session, $code, $currentTime);

            if (!$valid) {
                throw  new Exception("Invalid Code");
            }

            $user = $this->userSerivce->findByEmail($content['email']);

            if($user == null){
                throw new Exception("User not Found");
            }

            $user->setPassword(Randomizer::generateRandomPassword());
            $this->userSerivce->save($user);

            $payload = json_encode([
                'message' => "A Temporary Password Was Sent To your Email",
            ]);

            $response->getBody()->write($payload);

            return $response->withHeader('Content-Type', 'application/json');

        } catch (Exception $e) {

            $payload = json_encode([
                'message' => "Invalid Code",
            ]);

            $response->getBody()->write($payload);
            return $response
                ->withHeader('Content-Type', 'application/json')
                ->withStatus(400);

        }
    }

    public function newCode($request, $response, $args)
    {

        try {

            $content = $request->getParsedBody();

            $user = $this->userSerivce->findByEmail($content['email']);

            if ($user == null) {
                throw  new Exception("User not found");
            }

            session_regenerate_id();
            $this->codeModelService->createCode(Time::createFutureTime(5));

            $payload = json_encode([
                'message' => "Code Sent",
            ]);

            $response->getBody()->write($payload);
            return $response->withHeader('Content-Type', 'application/json');

        } catch (Exception $e) {

            $payload = json_encode([
                'message' => "User Not Found!",
            ]);

            $response->getBody()->write($payload);
            return $response
                ->withHeader('Content-Type', 'application/json')
                ->withStatus(400);
        }

    }

}
