<?php

namespace App\controller\auth;

use App\controller\admin\AdminAction;
use App\lib\Login;
use App\model\enum\UserRole;
use App\model\PrivilegesModel;
use App\model\UserModel;
use Exception;
use Psr\Http\Message\ResponseInterface as Response;
use Respect\Validation\Validator as V;

class RegisterAuth extends AdminAction
{

    /**
     * @inheritDoc
     */
    protected function action(): Response
    {

        if (!$this->systemSettingService->findById()->getAllowSignup()) {
            return $this->redirect("/signupNotAllowed");
        }

        $content = $this->getFormData();

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



            if($this->userService->isOccupied($content['block'],$content['lot'])){
                $data['nameError'] = "An account is already created for this property";
                $data['blockError'] = " ";
                $data['lotError'] = " ";
                throw new Exception('An account is already created for this property');
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

            $this->userService->save($user);
            $privileges = new PrivilegesModel();
            $privileges->setUserAnnouncement(true)
                ->setUserIssues(true)
                ->setUserPayment(true)
                ->setAdminIssues(false)
                ->setAdminPayment(false)
                ->setAdminAnnouncement(false)
                ->setAdminUser(false);

            $privileges->setUser($user);
            $this->privilegesService->save($privileges);

            Login::login($user->getId());

            $this->logLoginHistory();

            $name = $this->getLoginUser()->getName();

            $message = "Maligayang Pagdating sa Carissa Homes Subdivision Portal! $name
  Kasama ka na sa masayang komunidad ng Carissa Homes. Tara, mag-explore tayo!";

            $this->areaService->updateOwner($user);

            return $this->redirect('/home');

        } catch (Exception $e) {

            //error code for duplicate entry
            if ($e->getCode() == 1062) {
                $data['emailError'] = "Email Is Already In Used";
            }

            $data['content'] = $content;
            $data['error'] = $e->getMessage();

            return $this->view('pages/register.html',$data);
        }
    }
}