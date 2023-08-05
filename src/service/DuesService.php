<?php

namespace App\service;

use App\model\DuesModel;
use App\model\UserModel;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Exception\NotSupported;

class DuesService extends Service {

    /**
     * Save model to database
     * @param DuesModel $dues
     * @return void
     */
    public function save(DuesModel $dues)
    {
        $this->entityManager->persist($dues);
        $this->entityManager->flush($dues);
    }

    public function update(DuesModel $dues): void
    {

        $month = $dues->getMonth();
        $amount = $dues->getAmount();

        try {

            $em = $this->entityManager;
            $dues = $em->getRepository(DuesModel::class)->findOneBy(['month' => $month]);

            $dues->setAmount($amount);
            $this->entityManager->persist($dues);
            $this->entityManager->flush($dues);
        } catch (NotSupported $e) {
            $this->save($dues);
        }

    }


    public function findById($id): UserModel
    {
        $em = $this->entityManager;
        $dues = $em->find(DuesModel::class, $id);
        return $dues;
    }

    /**
     * Get the current due for this month
     */
    public function getDue($month)
    {

        $em = $this->entityManager;

        $qb = $em->createQueryBuilder();

        $qb->select('u.amount')
            ->from(DuesModel::class, 'u')
            ->where($qb->expr()->between('u.month', 'u.month', ':month'))
            ->setParameter('month', $month)
            ->orderBy('u.month', 'DESC')
            ->setMaxResults(1);

        $query = $qb->getQuery();
        $result = $query->getSingleScalarResult();

        return $result;
    }

    /**
     * Check if a save point is present on db
     * Note: Save point is not affected by change on other save point
     * @param $month
     * @return bool
     */
    public function isSavePoint($month): bool{
        try {
            $em = $this->entityManager;
            $dues = $em->getRepository(DuesModel::class)->findOneBy(['month' => $month]);
            return $dues != null;
        } catch (NotSupported $e) {
            return false;
        }
    }


}
