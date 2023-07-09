<?php

namespace App\service;

use App\model\UserModel;
use Doctrine\ORM\EntityManager;

class UserService extends Service {

    /**
     * Save model to database
     * @param UserModel user model
     * @return void
     */
    public function save(UserModel $user) {
        try {
            $this->entityManager->persist($user);
            $this->entityManager->flush($user);
        } catch (\PDOException $th) {
            throw $th;
        }
    }

    public function getManager(){
        return $this->entityManager;
    }
}
