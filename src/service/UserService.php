<?php

namespace App\service;

use App\exception\UserNotFoundException;
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

    /**
     * @throws UserNotFoundException
     */
    public function findById($id): UserModel
    {
        $em = $this->entityManager;
        $user = $em->find(UserModel::class, $id);

        if (!isset($user)) {
            throw new UserNotFoundException("User with id of $id not found");
        }

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

    /**
     * @throws UserNotFoundException
s     */
    public function findByEmail($email): UserModel
    {
        $em = $this->entityManager;
        $user =  $em->getRepository(UserModel::class)
            ->findOneBy(['email' => $email]);

        if(!isset($user)){
            throw new UserNotFoundException('User not found');
        }

        return  $user;
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


    /**
     * @throws UserNotFoundException
     */
    public function getUser($email, $password): UserModel
    {

        $queryBuilder = $this->entityManager->createQueryBuilder();
        $queryBuilder
            ->select('u')
            ->from(UserModel::class, 'u')
            ->where('u.email = :email')
            ->setParameter('email', $email);

        $user = $queryBuilder->getQuery()->getOneOrNullResult();


        if (!isset($user)) {
            throw new UserNotFoundException('User Not Found');
        }

        if ($user->getPassword() !== $password) {
            throw new UserNotFoundException('User Not Found');
        }

        return $user;
    }

    public function getAll($page, $max, $query, $role = 'admin', $block = null, $lot = null)
    {

        $paginator = new Paginator();

        $qb = $this->entityManager->createQueryBuilder();
        $or = $qb->expr()->orX();

        $qb->select('t')
            ->from(UserModel::class, 't')
            ->where($qb->expr()->eq('t.role', ':role'))
            ->orderBy('t.name','ASC')
            ->setParameter('role', $role);


        if (isset($block)) {
            $qb->andWhere($qb->expr()->eq('t.block',':block'));
            $qb->setParameter('block',$block);
        }

        if (isset($lot)) {
            $qb->andWhere($qb->expr()->eq('t.lot',':lot'));
            $qb->setParameter('lot',$lot);
        }

        if (isset($query)) {
            $or->addMultiple(
                [
                    $qb->expr()->like('t.name', ':query'),
                    $qb->expr()->like('t.id', ':query'),
                    $qb->expr()->like('t.email', ':query'),
                ]);

            $qb->setParameter('query', '%' . $query . '%');

            $qb->andWhere($or);
        }

        return $paginator->paginate($qb, $page, $max);
    }


    public function getStaffs()
    {

        $qb = $this->entityManager->createQueryBuilder();

        return $qb->select('t')
            ->from(UserModel::class, 't')
            ->where($qb->expr()->eq('t.role', ':role'))
            ->orderBy('t.name')
            ->setParameter('role', 'admin')
            ->getQuery()
            ->getResult();

    }

    public function isEmailInUsed(string $email): bool
    {

        $qb = $this->entityManager->createQueryBuilder();

        return $qb->select('COUNT(t)')
            ->from(UserModel::class, 't')
            ->where($qb->expr()->eq('t.email', ':email'))
            ->setParameter('email', $email)
            ->getQuery()
            ->getSingleScalarResult() > 0;

    }

    public function isOccupied(int $block, int $lot): bool
    {

        $qb = $this->entityManager->createQueryBuilder();

        return $qb->select('COUNT(t)')
                ->from(UserModel::class, 't')
                ->where(
                    $qb->expr()->andX(
                    $qb->expr()->eq('t.block', ':block'),
                    $qb->expr()->eq('t.lot', ':lot'),
                    $qb->expr()->like('t.email', ':email')
                    )
                )
                ->setParameter('block', $block)
                ->setParameter('lot', $lot)
                ->setParameter('email', '%@manual.payment')
                ->getQuery()
                ->getSingleScalarResult() > 0;

    }

}