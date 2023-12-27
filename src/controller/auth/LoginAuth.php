<?php

namespace App\controller\auth;

use App\controller\admin\AdminAction;
use App\exception\InvalidInput;
use App\exception\UserNotFoundException;
use App\lib\Login;
use App\lib\LoginDetails;
use App\lib\Redirector;
use App\model\enum\UserRole;
use App\model\LoginHistoryModel;
use Exception;
use Psr\Http\Message\ResponseInterface as Response;
use Respect\Validation\Validator as v;

class LoginAuth extends AdminAction
{

    /**
     * @inheritDoc
     */
    protected function action(): Response
    {

        $data = [];

        try {

            $content = $this->getFormData();

            $email = $content['email'];
            $password = $content['password'];

            if (!v::email()->notEmpty()->validate($email)) {
                throw new InvalidInput('Invalid Email');
            }

            if (!v::stringVal()->notEmpty()->validate($password)) {
                throw new InvalidInput('Password is required.');
            }

            $user = $this->userService->getUser($email, $password);

            Login::login($user->getId());

            $this->logLoginHistory();

            switch ($user->getRole()) {
                case UserRole::user():
                    return $this->redirect('/user/home');
                case UserRole::admin():
                    return $this->redirect(Redirector::redirectToHome($user->getPrivileges()));
                case UserRole::superAdmin():
                    return $this->redirect('/admin/payments');
                default:
                    throw new Exception('Invalid Role');
            }

        } catch (UserNotFoundException $userNotFoundException) {
            $data['loginError'] = 'Incorrect Email or Password';
        } catch (InvalidInput $invalidInput) {
            $data['loginError'] = $invalidInput->getMessage();
        } catch (Exception $ex) {
            $data['loginError'] = 'An Internal Error Occurred';
        }

        return $this->view('pages/login.html', $data);
    }


}