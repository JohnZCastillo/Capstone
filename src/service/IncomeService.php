<?php

namespace App\service;

use App\model\budget\FundModel;
use App\model\budget\IncomeModel;

class IncomeService extends Service
{

    public function save(IncomeModel $incomeModel)
    {
        $this->entityManager->persist($incomeModel);
        $this->entityManager->flush($incomeModel);
    }

    public function findById($id): IncomeModel|null
    {
        return $this
            ->entityManager
            ->find(IncomeModel::class, $id);
    }

    public function getAll(): array
    {
        return $this
            ->entityManager
            ->getRepository(IncomeModel::class)
            ->findAll();
    }

    public function getRecentIncome(int $max = 10): array
    {

        $qb = $this->entityManager->createQueryBuilder();

       return $qb->select('i')
            ->from(IncomeModel::class,'i')
            ->orderBy( 'i.createdAt','DESC')
            ->setMaxResults($max)
            ->getQuery()
            ->getResult();
    }

}
