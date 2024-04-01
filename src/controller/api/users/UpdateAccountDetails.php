<?php

namespace App\controller\api\users;

use App\controller\admin\AdminAction;
use App\exception\InvalidInput;
use App\exception\NotAuthorizeException;
use App\exception\UserNotFoundException;
use App\exception\users\EmailInUse;
use PHPUnit\Logging\Exception;
use Psr\Http\Message\ResponseInterface as Response;
use Respect\Validation\Validator as v;

class UpdateAccountDetails extends AdminAction
{
    protected function action(): Response
    {
        try {

            $formData = $this->getFormData();

            $userId = $formData['userId'];


            $user = $this->userService->findById($userId);


            $email = $formData['email'];
            $name = $formData['name'];

            if ($this->getLoginUser()->getId() != $userId) {
                throw new NotAuthorizeException("Cannot Change others Details");
            }

            if (!v::email()->validate($email)) {
                throw new InvalidInput('Invalid Email');
            }


            if (!v::alnum(' ')->notEmpty()->validate($name)) {
                throw new InvalidInput('Name must be string');
            }

            if($this->userService->isEmailInUsed($email) && $email != $user->getEmail()){
                throw new EmailInUse('Email is already used');
            }

            if($email !== $user->getEmail()){
                $user->setVerified(false);
            }

            $user->setEmail($email);
            $user->setName($name);

            $this->userService->save($user);

            return $this->respondWithData([
                "email" => $user->getEmail(),
                "name" => $user->getName(),
            ]);

        }  catch (EmailInUse $emailInUse) {
            return $this->respondWithData(["message" => $emailInUse->getMessage()], 400);
        }catch (InvalidInput $invalidInput) {
            return $this->respondWithData(["message" => $invalidInput->getMessage()], 400);
        } catch (NotAuthorizeException $notAuthorizeException) {
            return $this->respondWithData(["message" => $notAuthorizeException->getMessage()], 401);
        } catch (UserNotFoundException $userNotFoundException) {
            return $this->respondWithData(["message" => $userNotFoundException->getMessage()], 404);
        } catch (\Exception $e) {
            return $this->respondWithData(["message" => $e->getMessage()], 500);
//            return $this->respondWithData(["message" => 'An Internal Error Occurred'], 500);
        }
    }
}