<?php

namespace App\service;

use App\lib\Paginator;
use App\lib\QueryHelper;
use App\model\LoginHistoryModel;
use App\model\LogsModel;
use App\model\TransactionModel;
use App\model\UserModel;
use DateTime;

class LogsService  extends Service {

    public function addLog(LogsModel $log): void{
        $this->entityManager->persist($log);
        $this->entityManager->flush($log);
    }

    public function getAll($page, $max, $filter, $user = null)
    {

        $em = $this->entityManager;

        $paginator = new Paginator();

        $qb = $em->createQueryBuilder();

        $qb->select('t')
            ->from(LogsModel::class, 't');

        $queryHelper = new QueryHelper($qb);

        $queryHelper->Where("t.user = :user", "user", $user)
                ->andWhere($qb->expr()->in('t.status', ':status'), "status", $filter['tag'])
                ->andWhereIn($qb->expr()->between('t.created_at', ":from",":to"),[
                    "key"=>"from",
                    "value"=>$filter['from']
                ],[
                    "key"=>"to",
                    "value"=>$filter['to']
                ]);

        return $paginator->paginate($queryHelper->getQuery(), $page, $max);
    }

}