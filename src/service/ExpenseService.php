<?php

namespace App\service;

use App\exception\fund\ExpenseNotFound;
use App\model\budget\ExpenseModel;
use Doctrine\ORM\Exception\ORMException;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\TransactionRequiredException;

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
        $expense =  $this
            ->entityManager
            ->find(ExpenseModel::class, $id);

        if(!isset($expense)){
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

}
