<?php

namespace App\controller\api;

use App\controller\admin\AdminAction;
use App\exception\InvalidInput;
use App\exception\NotAuthorizeException;
use App\exception\UserNotFoundException;
use Psr\Http\Message\ResponseInterface as Response;
use Respect\Validation\Validator as v;

class UpdatePassword extends AdminAction
{

    /**
     * @inheritDoc
     */
    protected function action(): Response
    {

        try {

            $formData = $this->getFormData();

            if (!isset($formData['currentPassword'],
                $formData['newPassword'],
                $formData['confirmPassword'],
                $formData['userId'])) {
                throw new \Exception("missing inputs");
            }

            $userId = $formData['userId'];

            $user = $this->userService->findById($userId);

            if ($user->getId() !== ($this->getLoginUser())->getId()) {
                throw new NotAuthorizeException('Unauthorized');
            }

            $password = $formData['currentPassword'];
            $newPassword = $formData['newPassword'];
            $confirmPassword = $formData['confirmPassword'];

            $strictInput = v::notEmpty()->alnum()->noWhitespace();

            if (!$strictInput->validate($password)) {
                throw new InvalidInput('Invalid Password');
            }

            if (!$strictInput->validate($newPassword)) {
                throw new InvalidInput('Invalid New Password');
            }

            if (!$strictInput->validate($confirmPassword)) {
                throw new InvalidInput('Invalid Password Confirmation');
            }

            if ($user->getPassword() != $password) {
                throw new NotAuthorizeException("Incorrect Password");
            }

            if (!v::equals($confirmPassword)->validate($newPassword)) {
                throw new InvalidInput("Password Confirmation Does Not Match");
            }

            $user->setPassword($newPassword);

            $this->userService->save($user);

            return $this->respondWithData(["message" => "password update"]);

        } catch (UserNotFoundException $userNotFoundException) {
            return $this->respondWithData(["message" => $userNotFoundException->getMessage()], '404');
        } catch (NotAuthorizeException $notAuthorizeException) {
            return $this->respondWithData(["message" => $notAuthorizeException->getMessage()], '401');
        } catch (\Exception $e) {
            return $this->respondWithData(["message" => 'An Internal Error Occurred'], '500');
        }

    }
}