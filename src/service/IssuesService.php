<?php

namespace App\service;

use App\exception\issue\IssueNotFoundException;
use App\lib\Paginator;
use App\lib\Time;
use App\model\IssuesModel;
use Doctrine\ORM\Exception\ORMException;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\TransactionRequiredException;

class IssuesService extends Service {

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

        $issue  = $this->entityManager->find(IssuesModel::class, $id);

        if(!isset($issue)){
            throw new IssueNotFoundException("Issue with id of $id not found");
        }

        return $issue;
    }

    public function findByTarget($target): IssuesModel|null
    {
        $em = $this->entityManager;

        $due = $em->getRepository(IssuesModel::class)
            ->findOneBy(['target' => $target]);

        return  $due;
    }

    public function getAll($page, $max, $id, $title,$status, $user = null, $type = 'posted', $createdAt = null)
    {

        $qb = $this->entityManager->createQueryBuilder();

        $or = $qb->expr()->andX();

        $qb->select('t')
            ->from(IssuesModel::class, 't')
            ->where($or);

        $or->add($qb->expr()->eq('t.type',':type'));
        $qb->setParameter('type',$type);

        $status = $status == 'ALL' ? null : $status;

        if(isset($id)){
            $or->add($qb->expr()->eq('t.id',':id'));
            $qb->setParameter('id',$id);
        }

        if(isset($user)){
            $or->add($qb->expr()->eq('t.user',':user'));
            $qb->setParameter('user',$user);
        }

        if(isset($status)){
            $or->add($qb->expr()->eq('t.status',':status'));
            $qb->setParameter('status',$status);
        }

        if(isset($title)){
            $or->add($qb->expr()->eq('t.title',':title'));
            $qb->setParameter('id',$title);
        }

        if(isset($cratedAt)){
            $createdEnd = Time::convertDateStringToDateTimeEndDay($createdAt);
            $createdStart = Time::convertDateStringToDateTimeStartDay($createdAt);
            $or->add($qb->expr()->between('t.createdAt',':startDate',':endDate'));
            $qb->setParameter('startDate',$createdStart);
            $qb->setParameter('endDate',$createdEnd);
        }

        $paginator = new Paginator();

        return $paginator->paginate($qb, $page, $max);
    }
}