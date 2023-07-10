<?php

namespace App\service;

use App\model\DuesModel;
use App\model\UserModel;
use Doctrine\ORM\EntityManager;

class DuesService extends Service {

    /**
     * Save model to database
     * @param DuesModel $dues 
     * @return void
     */
    public function save(DuesModel $dues) {
        $this->entityManager->persist($dues);
        $this->entityManager->flush($dues);
    }

    public function findById($id): UserModel {
        $em = $this->entityManager;
        $dues = $em->find(DuesModel::class, $id);
        return $dues;
    }

    /**
     * Get the current due for this month
     */
    public function getDue($month) {

        $em = $this->entityManager;

        $qb = $em->createQueryBuilder();

        $qb->select('u.amount')
            ->from(DuesModel::class, 'u')
            ->where($qb->expr()->between('u.month', 'u.month',':month'))
            ->setParameter('month', $month)
            ->orderBy('u.month', 'DESC')
            ->setMaxResults(1);

        $query = $qb->getQuery();
        $result = $query->getSingleScalarResult();

        return  $result;
    }

}
