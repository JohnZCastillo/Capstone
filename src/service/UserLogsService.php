<?php

namespace App\service;

use App\lib\Paginator;
use App\model\UserLogsModel;

class UserLogsService extends Service
{

    public function addLog(UserLogsModel $log): void
    {
        $this->entityManager->persist($log);
        $this->entityManager->flush($log);
    }

    public function getAll($user): array
    {

        $em = $this->entityManager;

        $qb = $em->createQueryBuilder();

        $qb->select('l')
            ->from(UserLogsModel::class, 'l')
            ->where('l.user = :user')
            ->setMaxResults(10)
            ->orderBy('l.created_at', "DESC");

        return $qb->getQuery()->getResult();
    }

}