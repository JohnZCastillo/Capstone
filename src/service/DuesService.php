<?php

namespace App\service;

use App\lib\Time;
use App\model\DuesModel;
use App\model\UserModel;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Exception\NotSupported;
use Exception;

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


    public  function createDue($month): DuesModel{

        $em = $this->entityManager;

         $due = $em->getRepository(DuesModel::class)
            ->findOneBy(['month' => $month]);

         if($due == null){
             return new DuesModel();
         }

         return  $due;
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

    /**
     * Return an array containing the monthly dues
     * @param int $year
     * @return array
     */
    public function getMonthlyDues(int $year): array
    {

        $dues = [];

        try {

            $datesForMonths = Time::getDatesForMonthsOfYear($year);

            foreach ($datesForMonths as $month => $dates) {
                $dues[] = [
                    "date" => $dates,
                    "amount" => $this->getDue($dates),
                    "savePoint" => $this->isSavePoint($dates),
                    "month" => $dates->format('M'),
                ];
            }

            return $dues;

        } catch (Exception $e) {
            return [];
        }
    }

}
