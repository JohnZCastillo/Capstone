<?php

namespace App\service;

use App\lib\Paginator;
use App\lib\QueryHelper;
use App\lib\Time;
use App\model\enum\UserRole;
use App\model\IssuesModel;
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

    public function findUsers($block = null, $lot = null): array {

        $queryBuilder = $this->entityManager->createQueryBuilder();
        $queryBuilder
            ->select('u')
            ->from(UserModel::class, 'u')
            ->where('u.name != :name')
            ->andWhere("u.role  = :role")
            ->setParameter('name', 'manual payment')
            ->setParameter('role', UserRole::user());

        if(isset($block)){
           $queryBuilder =  $queryBuilder->andWhere('u.block = :block')
               ->setParameter("block",$block);
        }

        if(isset($lot)){
            $queryBuilder =  $queryBuilder->andWhere('u.lot = :lot')
                ->setParameter("lot",$lot);
        }

        return $queryBuilder->getQuery()->getResult();
    }

    public function findByEmail($email): UserModel|null {
        $em = $this->entityManager;
        return $em->getRepository(UserModel::class)
            ->findOneBy(['email'=>$email]);
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

    public function getAll($page, $max, $id, $filter, $role = null, $type = '',)
    {

        $em = $this->entityManager;

        $paginator = new Paginator();

        $qb = $em->createQueryBuilder();

        $qb->select('t')
            ->from(UserModel::class, 't')
            ->where("t.role = :role")
            ->andWhere(
                $qb->expr()->orX(
                    $qb->expr()->like('t.name', ':queryParam'),
                    $qb->expr()->like('t.email', ':queryParam'),
                    $qb->expr()->like('t.id', ':queryParam')
                )
            )
            ->setParameter('queryParam', '%' . $id . '%')
            ->setParameter('role', $role);

        return $paginator->paginate($qb, $page, $max);
    }
}
