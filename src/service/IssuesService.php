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

        $queryHelper->Where("t.type = :type", "type", $type);

        if($user != null){
            $queryHelper->getQuery()->orWhere($queryHelper->getQuery()->expr()->isNull('t.user'));
        }

        $queryHelper
            ->andWhere("t.user = :user", "user", $user)
            ->andWhere("t.id like :id", "id", $id)
            ->andWhere("t.status = :status", "status", $filter['status']);

        if ($createdAt != null) {
            $createdEnd = Time::convertDateStringToDateTimeEndDay($createdAt);
            $createdAt = Time::convertDateStringToDateTimeStartDay($createdAt);

            $queryHelper->getQuery()->andWhere( $queryHelper->getQuery()->expr()->between('t.createdAt',":startDate",":endDate"));
            $queryHelper->getQuery()->setParameter(':startDate',$createdAt);
            $queryHelper->getQuery()->setParameter(':endDate',$createdEnd);
        }

        return $paginator->paginate($queryHelper->getQuery(), $page, $max);
    }
}