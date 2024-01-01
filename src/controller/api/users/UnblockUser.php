<?php

namespace App\controller\api\users;

use App\controller\admin\AdminAction;
use App\exception\UserNotFoundException;
use Psr\Http\Message\ResponseInterface as Response;

class UnblockUser extends AdminAction
{
    protected function action(): Response
    {
        try {

            $formData = $this->getFormData();

            $userId = $formData['userId'];

            $user = $this->userService->findById($userId);

            $user->setIsBlocked(false);

            $this->userService->save($user);

            return $this->respondWithData(["message" => "user has been unblocked"]);

        } catch (UserNotFoundException $userNotFoundException) {
            return $this->respondWithData(["message" => $userNotFoundException->getMessage(), 404]);
        } catch (\Exception $e) {
            return $this->respondWithData(["message" => 'Something went wrong', 404]);
        }
    }
}