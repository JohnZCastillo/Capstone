<?php

namespace App\service;

use App\lib\Paginator;
use App\lib\QueryHelper;
use App\lib\Time;
use App\model\IssuesModel;

// use App\model\IssuesModel;
// use App\model\UserModel;
// use Doctrine\ORM\EntityManager;

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

    public function findById($id): IssuesModel
    {
        $em = $this->entityManager;
        $dues = $em->find(IssuesModel::class, $id);
        return $dues;
    }

    public function getAll($page, $max, $id, $filter, $user = null, $type = 'posted', $createdAt = null)
    {

        $filter['status'] = $filter['status'] == 'ALL' ? null : $filter['status'];

        $em = $this->entityManager;

        $paginator = new Paginator();

        $qb = $em->createQueryBuilder();

        $qb->select('t')
            ->from(IssuesModel::class, 't');

        $queryHelper = new QueryHelper($qb);

        if ($createdAt != null) {
            $createdEnd = Time::convertDateStringToDateTimeEndDay($createdAt);
            $createdAt = Time::convertDateStringToDateTimeStartDay($createdAt);
        }

        $queryHelper->Where("t.type = :type", "type", $type)
            ->andWhere("t.user = :user", "user", $user)
            ->andWhere("t.id like :id", "id", $id)
            ->andWhere("t.createdAt >= :createdAt", "createdAt", $createdAt)
            ->andWhere("t.createdAt <= :createdEnd", "createdEnd", $createdEnd)
            ->andWhere("t.status = :status", "status", $filter['status']);

        $queryHelper->getQuery()->orWhere('t.user is null');

        var_dump($queryHelper->getQuery()->getQuery()->getSQL());

        return $paginator->paginate($queryHelper->getQuery(), $page, $max);
    }
}