<?php

namespace App\service;

use App\exception\announcement\AnnouncementNotFound;
use App\lib\Paginator;
use App\model\AnnouncementModel;

class AnnouncementService extends Service
{

    /**
     * Save model to database
     * @param AnnouncementModel $dues
     * @return void
     */
    public function save(AnnouncementModel $dues)
    {
        $this->entityManager->persist($dues);
        $this->entityManager->flush($dues);
    }

    public function delete(AnnouncementModel $dues)
    {
        $this->entityManager->remove($dues);
        $this->entityManager->flush($dues);
    }

    public function findById($id): AnnouncementModel
    {
        $dues = $this->entityManager->find(AnnouncementModel::class, $id);

        if (!isset($dues)) {
            throw new AnnouncementNotFound("Announcement with id of $id not found");
        }

        return $dues;
    }

    public function findAll(): array
    {

        $qb = $this->entityManager->createQueryBuilder();

        return $qb->select('t')
            ->from(AnnouncementModel::class, 't')
            ->where('t.status = :status')
            ->setParameter('status', 'posted')
            ->addOrderBy('t.pinDate', 'DESC')
            ->addOrderBy('t.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }


    public function getAll($page, $max, $id, $from, $to, $status = 'posted')
    {

        $qb = $this->entityManager->createQueryBuilder();

        $or = $qb->expr()->orX();

        $qb->select('t')
            ->from(AnnouncementModel::class, 't')
            ->where($or)
            ->addOrderBy('t.pinDate', 'DESC')
            ->addOrderBy('t.createdAt', 'ASC');

        $or->add($qb->expr()->eq('t.status', ':status'));
        $qb->setParameter('status', $status);

        if (isset($id)) {
            $or->add($qb->expr()->like('t.id', ':id'));
            $qb->setParameter('id', $id);
        }

        if (isset($from)) {
            $or->add($qb->expr()->gte('t.createdAt', ':from'));
            $qb->setParameter('from', $from);
        }

        if (isset($to)) {
            $or->add($qb->expr()->lte('t.createdAt', ':to'));
            $qb->setParameter('to', $to);
        }

        $paginator = new Paginator();
        $paginator->paginate($qb, $page, $max);

        return [
            'result' => $paginator->getItems(),
            'paginator' => $paginator
        ];
    }


}
