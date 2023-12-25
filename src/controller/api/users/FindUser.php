<?php

namespace App\controller\api\users;

use App\controller\admin\AdminAction;
use App\exception\UserNotFoundException;
use Psr\Http\Message\ResponseInterface as Response;

class FindUser extends AdminAction
{

    /**
     * @inheritDoc
     */
    protected function action(): Response
    {
        try {

            $formData = $this->getFormData();

            $email = $formData['email'];

            $user = $this->userService->findByEmail($email);

            $data = ['name' => $user->getName(),
                'payment' => $user->getPrivileges()->getAdminPayment(),
                'issue' => $user->getPrivileges()->getAdminIssues(),
                'announcement' => $user->getPrivileges()->getAdminAnnouncement(),
                'user' => $user->getPrivileges()->getAdminUser(),
            ];

            return $this->respondWithData($data);
        } catch (UserNotFoundException $userNotFoundException) {
            return $this->respondWithData(['message' => $userNotFoundException->getMessage()], 404);
        } catch (\Exception $e) {
            return $this->respondWithData(['message' => 'An Internal Error Occurred'], 500);
        }
    }
}