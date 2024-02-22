<?php

namespace App\service;

use App\exception\issue\IssueNotFoundException;
use App\lib\Paginator;
use App\lib\Time;
use App\model\enum\IssuesStatus;
use App\model\IssuesModel;
use App\model\TransactionModel;
use Doctrine\ORM\Exception\ORMException;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\TransactionRequiredException;

class IssuesService extends Service
{

    /**
     * Save model to database
     * @param IssuesModel $dues
     * @return void
     */
    public function save(IssuesModel $dues)
    {
        $this->entityManager->persist($dues);
        $this->entityManager->flush($dues);
    }

    /**
     * @throws OptimisticLockException
     * @throws ORMException
     * @throws IssueNotFoundException
     * @throws TransactionRequiredException
     */
    public function findById($id): IssuesModel
    {

        $issue = $this->entityManager->find(IssuesModel::class, $id);

        if (!isset($issue)) {
            throw new IssueNotFoundException("Issue with id of $id not found");
        }

        return $issue;
    }

    public function findByTransaction(TransactionModel $transaction): IssuesModel|null
    {
        $em = $this->entityManager;

        $due = $em->getRepository(IssuesModel::class)
            ->findOneBy(['transaction' => $transaction]);

        return $due;
    }

    public function getAll($page, $max, $id, $status, $user = null, $createdAt = null)
    {

        $qb = $this->entityManager->createQueryBuilder();

        $or = $qb->expr()->andX();

        $qb->select('t')
            ->from(IssuesModel::class, 't')
            ->where($or);

        $status = empty($status) ? [IssuesStatus::PENDING] : [$status];

        if (isset($id)) {
            $or->add($qb->expr()->eq('t.id', ':id'));
            $qb->setParameter('id', $id);
        }

        if (isset($user)) {
            $or->add($qb->expr()->eq('t.user', ':user'));
            $qb->setParameter('user', $user);
        }

        $or->add($qb->expr()->in('t.status', ':status'));
        $qb->setParameter('status', $status);

        if (isset($cratedAt)) {
            $createdEnd = Time::convertDateStringToDateTimeEndDay($createdAt);
            $createdStart = Time::convertDateStringToDateTimeStartDay($createdAt);
            $or->add($qb->expr()->between('t.createdAt', ':startDate', ':endDate'));
            $qb->setParameter('startDate', $createdStart);
            $qb->setParameter('endDate', $createdEnd);
        }

        $paginator = new Paginator();

        return $paginator->paginate($qb, $page, $max);
    }
}