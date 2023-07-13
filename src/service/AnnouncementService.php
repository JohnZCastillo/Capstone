<?php

namespace App\service;

use App\lib\Helper;
use App\model\AnnouncementModel;

class AnnouncementService extends Service {

    /**
     * Save model to database
     * @param AnnouncementModel $dues 
     * @return void
     */
    public function save(AnnouncementModel $dues) {
        $this->entityManager->persist($dues);
        $this->entityManager->flush($dues);
    }
    
    public function delete(AnnouncementModel $dues) {
        $this->entityManager->remove($dues);
        $this->entityManager->flush($dues);
    }

    public function findById($id): AnnouncementModel {
        $em = $this->entityManager;
        $dues = $em->find(AnnouncementModel::class, $id);
        return $dues;
    }

    
    public function getAll($page, $max, $id,$filter, $user = null) {

        // Step 1: Define pagination settings
        $transactionsPerPage = $max;
        $currentPage = $page; // Set the current page based on user input or any other criteria

        $em = $this->entityManager;

        // Step 3: Fetch paginated announcements
        $queryBuilder = $em->createQueryBuilder();
        $queryBuilder->select('t')
            ->from(AnnouncementModel::class, 't');

        if(Helper::existAndNotNull($user)){
            $queryBuilder->where('t.user = :user')
            ->setParameter('user', $user);
        }

        if (Helper::existAndNotNull($id)) {
            $queryBuilder->andWhere('t.id like :id')->setParameter('id', $id);
        }

        if (Helper::existAndNotNull($filter,'from')) {
            $queryBuilder->andWhere('t.createdAt >= :from')->setParameter('from', $filter['from']);
        }

        if (Helper::existAndNotNull($filter,'to')) {
            $queryBuilder->andWhere('t.createdAt <= :to')->setParameter('to', $filter['to']);
        }

        if (Helper::existAndNotNull($filter,'status')) {
            if($filter['status'] != 'ALL'){
                $queryBuilder->andWhere('t.status = :status')->setParameter('status', $filter['status']);
            }
        }

        $queryBuilder->setMaxResults($transactionsPerPage)
            ->setFirstResult(($currentPage - 1) * $transactionsPerPage);

        // Step 4: Execute the query and retrieve announcements
        $announcements = $queryBuilder->getQuery()->getResult();

        $queryBuilder = $em->createQueryBuilder();
        $queryBuilder->select('count(t.id)')
            ->from(AnnouncementModel::class, 't');

            if(Helper::existAndNotNull($user)){
                $queryBuilder->where('t.user = :user')
                ->setParameter('user', $user);
            }
    
            if (Helper::existAndNotNull($id)) {
                $queryBuilder->andWhere('t.id like :id')->setParameter('id', $id);
            }
    
            if (Helper::existAndNotNull($filter,'from')) {
                $queryBuilder->andWhere('t.createdAt >= :from')->setParameter('from', $filter['from']);
            }
    
            if (Helper::existAndNotNull($filter,'to')) {
                $queryBuilder->andWhere('t.createdAt <= :to')->setParameter('to', $filter['to']);
            }
    
            if (Helper::existAndNotNull($filter,'status')) {
                if($filter['status'] != 'ALL'){
                    $queryBuilder->andWhere('t.status = :status')->setParameter('status', $filter['status']);
                }
            }

        $totalAnnouncement = $queryBuilder->getQuery()->getSingleScalarResult();

        return [
            'announcements' => $announcements,
            'totalAnnouncement' => $totalAnnouncement
        ];
    }

    
}
