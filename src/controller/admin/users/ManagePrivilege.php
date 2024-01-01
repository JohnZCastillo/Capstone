<?php

namespace App\controller\admin\users;

use App\controller\admin\AdminAction;
use App\exception\InvalidInput;
use App\exception\UserNotFoundException;
use App\model\enum\UserRole;
use Exception;
use Psr\Http\Message\ResponseInterface as Response;
use Respect\Validation\Validator as v;

class ManagePrivilege extends AdminAction
{
    protected function action(): Response
    {
        $formData = $this->getFormData();

        try {

            $admin = false;
            $email = $formData['email'];

            if (!(v::email()->notEmpty()->validate($email))) {
                throw new InvalidInput('Invalid Email');
            }

            $user = $this->userService->findByEmail($email);

            $managePayments = $formData['payment'] ?? null;
            $manageIssues = $formData['issue'] ?? null;
            $manageAnnouncements = $formData['announcement'] ?? null;
            $manageUsers = $formData['user'] ?? null;

            $user->setRole(UserRole::admin());
            $this->userService->save($user);

            if (isset($managePayments)) {
                $user->getPrivileges()->setAdminPayment(true);
                $admin = true;
            } else {
                $user->getPrivileges()->setAdminPayment(false);
            }

            if (isset($manageIssues)) {
                $user->getPrivileges()->setAdminIssues(true);
                $admin = true;

            } else {
                $user->getPrivileges()->setAdminIssues(false);
            }

            if (isset($manageAnnouncements)) {
                $user->getPrivileges()->setAdminAnnouncement(true);
                $admin = true;

            } else {
                $user->getPrivileges()->setAdminAnnouncement(false);

            }

            if (isset($manageUsers)) {
                $user->getPrivileges()->setAdminUser(true);
                $admin = true;

            } else {
                $user->getPrivileges()->setAdminUser(false);
            }

            if ($admin) {
                $user->setRole(UserRole::admin());
            } else {
                $user->setRole(UserRole::user());
            }

            $this->userService->save($user);

            $action = "User with id of " . $user->getId() . " update privileges";

            $this->addActionLog($action, 'Admin');

            $this->privilegesService->save($user->getPrivileges());

            $this->addSuccessMessage('Privileges Updated');
        } catch (InvalidInput $invalidInput) {
            $this->addErrorMessage($invalidInput->getMessage());
        } catch (UserNotFoundException $userNotFoundException) {
            $this->addErrorMessage($userNotFoundException->getMessage());
        } catch (Exception $e) {
            $this->addErrorMessage('An Internal Error Occurred');
        }
        return $this->redirect("/admin/users");
    }
}