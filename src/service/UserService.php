<?php

namespace App\service;

use App\model\UserModel;
use Doctrine\ORM\EntityManager;

class UserService extends Service {

    public function save(UserModel $user) {
        $this->entityManager->persist($user);
        $this->entityManager->flush($user);
    }
    
}
