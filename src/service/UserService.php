<?php

namespace App\service;

use App\lib\Paginator;
use App\model\enum\UserRole;
use App\model\UserModel;

class UserService extends Service
{

    /**
     * Save model to database
     * @param UserModel user model
     * @return void
     */
    public function save(UserModel $user)
    {
        $this->entityManager->persist($user);
        $this->entityManager->flush($user);
    }

    public function findById($id): UserModel
    {
        $em = $this->entityManager;
        $user = $em->find(UserModel::class, $id);
        return $user;
    }

    public function findUsers($block = null, $lot = null): array
    {

        $queryBuilder = $this->entityManager->createQueryBuilder();
        $queryBuilder
            ->select('u')
            ->from(UserModel::class, 'u')
            ->where('u.name != :name')
            ->andWhere("u.role  = :role")
            ->setParameter('name', 'manual payment')
            ->setParameter('role', UserRole::user());

        if (isset($block)) {
            $queryBuilder = $queryBuilder->andWhere('u.block = :block')
                ->setParameter("block", $block);
        }

        if (isset($lot)) {
            $queryBuilder = $queryBuilder->andWhere('u.lot = :lot')
                ->setParameter("lot", $lot);
        }

        return $queryBuilder->getQuery()->getResult();
    }

    public function findByEmail($email): UserModel|null
    {
        $em = $this->entityManager;
        return $em->getRepository(UserModel::class)
            ->findOneBy(['email' => $email]);
    }

    public function findManualPayment(string $block, string $lot): UserModel|null
    {

        $email = $block . $lot . "@manual.payment";

        $em = $this->entityManager;

        $user = $em->getRepository(UserModel::class)
            ->findOneBy(['email' => $email]);

        if (!isset($user)) {

            $user = new UserModel();

            $user->setName("manual payment")
                ->setBlock($block)
                ->setLot($lot)
                ->setRole(UserRole::user())
                ->setPassword("")
                ->setEmail($email)
                ->setIsBlocked(false);

            $this->save($user);
        }

        return $user;
    }


    public function getUser($email, $password)
    {

        $queryBuilder = $this->entityManager->createQueryBuilder();
        $queryBuilder
            ->select('u')
            ->from(UserModel::class, 'u')
            ->where('u.email = :email')
            ->setParameter('email', $email);

        $user = $queryBuilder->getQuery()->getOneOrNullResult();


        if (!isset($user)) {
            return null;
        }

        if ($user->getPassword() !== $password) {
            return null;
        }

        return $user;
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
