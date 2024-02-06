<?php

namespace App\fixtures;

use App\model\enum\UserRole;
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
            $user->setName($admin->name);
            $user->setRole(UserRole::admin());
            $user->setPassword($admin->password);
            $user->setEmail($admin->email);
            $user->setBlock($admin->blocl);
            $user->setLot($admin->lot);

            $manager->persist($user);
            $manager->flush();
        }
    }
}