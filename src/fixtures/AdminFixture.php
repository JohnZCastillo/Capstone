<?php

namespace App\fixtures;

use App\model\enum\UserRole;
use App\model\PrivilegesModel;
use App\model\UserModel;
use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Persistence\ObjectManager;

class AdminFixture implements FixtureInterface
{

    protected array $admins = [
        [
            'name' => 'admin',
            'password' => 'password',
            'email' => 'admin@admin.com',
            'block' => '0',
            'lot' => '0',
        ],
    ];

    public function load(ObjectManager $manager)
    {

        foreach ($this->admins as $index => $admin){

            $user = new UserModel();
            $user->setName($admin['name']);
            $user->setRole(UserRole::superAdmin());
            $user->setPassword($admin['password']);
            $user->setEmail($admin['email']);
            $user->setBlock($admin['block']);
            $user->setLot($admin['lot']);
            $user->setIsBlocked(false);

            $manager->persist($user);
            $manager->flush();

            $privilege = $this->createPrivileges($user);

            $manager->persist($privilege);
            $manager->flush();

            $user->setPrivileges($privilege);

            $manager->persist($user);
            $manager->flush();

        }
    }

    private function createPrivileges(UserModel $userModel)
    {
        $privilege = new PrivilegesModel();
        $privilege->setUser($userModel);
        $privilege->setAdminAnnouncement(true);
        $privilege->setAdminIssues(true);
        $privilege->setAdminPayment(true);
        $privilege->setAdminUser(true);
        $privilege->setAdminUser(true);
        $privilege->setUserAnnouncement(true);
        $privilege->setUserIssues(true);
        $privilege->setUserPayment(true);

        return $privilege;
    }
}