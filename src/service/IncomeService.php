<?php

namespace App\service;

use App\model\budget\FundModel;
use App\model\budget\IncomeModel;
use App\model\TransactionModel;

class IncomeService extends Service
{

    public function save(IncomeModel $incomeModel)
    {
        $this->entityManager->persist($incomeModel);
        $this->entityManager->flush($incomeModel);
    }

    public function delete(TransactionModel $transaction): void
    {
        $qb = $this->entityManager->createQueryBuilder();

        $qb->delete(IncomeModel::class,'i')
            ->where($qb->expr()->eq('i.transaction',':transaction'))
            ->setParameter('transaction',$transaction)
            ->getQuery()
            ->getResult();

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

    public function getRecentIncome(FundModel $fundModel,int $max = 10): array
    {

        $qb = $this->entityManager->createQueryBuilder();

       return $qb->select('i')
            ->from(IncomeModel::class,'i')
           ->where($qb->expr()->eq('i.fund',':fund'))
           ->setParameter('fund',$fundModel)
            ->orderBy( 'i.createdAt','DESC')
            ->setMaxResults($max)
            ->getQuery()
            ->getResult();
    }

}
