<?php

namespace App\controller\api\users;

use App\controller\admin\AdminAction;
use App\exception\UserNotFoundException;
use App\exception\users\UserBlockCooldown;
use App\lib\Time;
use Psr\Http\Message\ResponseInterface as Response;

class BlockUser extends AdminAction
{
    protected function action(): Response
    {
        try {

            $userId = $this->args['id'];

            $user = $this->userService->findById($userId);

            $lastBlockedDate = $user->getBlockDate();

            if (isset($lastBlockedDate)) {

                $todayDateString = Time::convert(new \DateTime());
                $lastBlockedString = Time::convert($lastBlockedDate);

                $pastDay = Time::dayPast($todayDateString, $lastBlockedString);

                if ($pastDay <= 0) {
                    throw  new UserBlockCooldown('Access to this feature is temporarily restricted due to a cooldown period in effect. Please try again after 1 day.');
                }
            }

            $user->setBlockDate(new \DateTime());
            $user->setIsBlocked(true);

            $this->userService->save($user);

            return $this->respondWithData(["message" => "user has been blocked"]);

        } catch (UserBlockCooldown $userBlockCooldown) {
            return $this->respondWithData(["message" => $userBlockCooldown->getMessage()], 400);
        } catch (UserNotFoundException $userNotFoundException) {
            return $this->respondWithData(["message" => $userNotFoundException->getMessage()], 400);
        } catch (\Exception $e) {
            return $this->respondWithData(["message" => 'Something went wrong'], 500);
        }
    }
}