<?php

namespace App\service;

use App\lib\Paginator;
use App\model\LogsModel;
use DateTime;

class LogsService extends Service
{

    public function addLog(LogsModel $log): void
    {
        $this->entityManager->persist($log);
        $this->entityManager->flush($log);
    }

    public function getAll($page, $max, $status, $from = null, $to = null, $user = null)
    {

        $qb = $this->entityManager->createQueryBuilder();

        $qb->select('t')
            ->from(LogsModel::class, 't')
            ->orderBy('t.created_at','DESC');

        $or = $qb->expr()->andX();

        $paginator = new Paginator();

        $notEmpty = false;

        if (isset($user)) {
            $or->add($qb->expr()->eq('t.user', ':user'));
            $qb->setParameter('user', $user);
            $notEmpty = true;
        }

        if (isset($status)) {
            $or->add($qb->expr()->eq('t.tag', ':status'));
            $qb->setParameter('status', $status);
            $notEmpty = true;

        }

        if (isset($from, $to)) {

            $from = (new DateTime($from))->format('Y-m-d H:i:s');
            $to = new DateTime($to);
            $to->setTime(23, 59, 59, 59);

            $to = $to->format('Y-m-d H:i:s');

            $or->add($qb->expr()->between('t.created_at', ':from', ':to'));
            $qb->setParameter('from', $from);
            $qb->setParameter('to', $to);
            $notEmpty = true;
        }

        if ($notEmpty) {
            $qb->where($or);
        }

        return $paginator->paginate($qb, $page, $max);
    }

}