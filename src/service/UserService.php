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
        $this->entityManager->persist($user);
        $this->entityManager->flush($user);
    }

    public function findById($id): UserModel {
        $em = $this->entityManager;
        $user = $em->find(UserModel::class, $id);
        return $user;
    }

    public function getUser($email, $password) {

        $queryBuilder = $this->entityManager->createQueryBuilder();
        $queryBuilder
            ->select('u')
            ->from(UserModel::class, 'u')
            ->where('u.email = :email')
            ->andWhere('u.password = :password')
            ->setParameter('email', $email)
            ->setParameter('password', $password);

        return $queryBuilder->getQuery()->getOneOrNullResult();
    }
}
