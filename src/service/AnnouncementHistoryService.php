<?php

namespace App\service;

use App\exception\announcement\AnnouncementNotFound;
use App\lib\Paginator;
use App\model\AnnouncementHistoryModel;
use App\model\AnnouncementModel;

class AnnouncementHistoryService extends Service
{
    public function save(AnnouncementHistoryModel $model)
    {
        $this->entityManager->persist($model);
        $this->entityManager->flush($model);
    }

    public function findById($id): AnnouncementHistoryModel
    {

        $dues = $this->entityManager->find(AnnouncementHistoryModel::class, $id);

        if(!isset($dues)){
            throw new AnnouncementNotFound("Announcement with id of $id is missing");
        }

        return $dues;
    }


    public function getAll($page, $max, AnnouncementModel $announcement): array
    {

        $qb = $this->entityManager->createQueryBuilder();
        $qb->select('h')
            ->from(AnnouncementHistoryModel::class, 'h')
            ->where($qb->expr()->eq('t.announcement', ':announcement'))
            ->setParameter('announcement', $announcement);

        $paginator = new Paginator();
        $paginator->paginate($qb, $page, $max);

        return [
            'result' => $paginator->getItems(),
            'paginator' => $paginator
        ];
    }

}
