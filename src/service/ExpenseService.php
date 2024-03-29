<?php

namespace App\service;

use App\exception\fund\ExpenseNotFound;
use App\model\budget\ExpenseModel;
use App\model\budget\FundModel;
use App\model\enum\BudgetStatus;

class ExpenseService extends Service
{

    public function save(ExpenseModel $expenseModel)
    {
        $this->entityManager->persist($expenseModel);
        $this->entityManager->flush($expenseModel);
    }

    /**
     * @throws ExpenseNotFound
     */
    public function findById($id): ExpenseModel
    {
        $expense = $this
            ->entityManager
            ->find(ExpenseModel::class, $id);

        if (!isset($expense)) {
            throw new ExpenseNotFound("Expense with id of $id is missing");
        }

        return $expense;
    }

    public function getAll(): array
    {
        return $this
            ->entityManager
            ->getRepository(ExpenseModel::class)
            ->findAll();
    }

    public function getRecentIncome(FundModel $fundModel,int $max = 10): array
    {

        $qb = $this->entityManager->createQueryBuilder();

        return $qb->select('e')
            ->from(ExpenseModel::class, 'e')
            ->where(
                $qb->expr()->andX(

                $qb->expr()->eq('e.status',':status'),
                $qb->expr()->eq('e.fund',':fund')
                )
            )
            ->orderBy('e.createdAt', 'DESC')
            ->setParameter('status', BudgetStatus::approved())
            ->setParameter('fund', $fundModel)
            ->setMaxResults($max)
            ->getQuery()
            ->getResult();
    }

}
