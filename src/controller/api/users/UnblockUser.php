<?php

namespace App\controller\api\users;

use App\controller\admin\AdminAction;
use App\exception\UserNotFoundException;
use App\model\enum\LogsTag;
use Psr\Http\Message\ResponseInterface as Response;

class UnblockUser extends AdminAction
{
    protected function action(): Response
    {
        try {

            $userId = $this->args['id'];

            $user = $this->userService->findById($userId);

            $user->setIsBlocked(false);

            $this->userService->save($user);

            $this->addActionLog("User with id of $userId was unblocked", LogsTag::userBlock());

            return $this->respondWithData(["message" => "user has been unblocked"]);

        } catch (UserNotFoundException $userNotFoundException) {
            return $this->respondWithData(["message" => $userNotFoundException->getMessage(), 404]);
        } catch (\Exception $e) {
            return $this->respondWithData(["message" => 'Something went wrong', 404]);
        }
    }
}