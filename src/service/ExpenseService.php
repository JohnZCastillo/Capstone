<?php

namespace App\service;

use App\model\budget\ExpenseModel;

class ExpenseService extends Service
{

    public function save(ExpenseModel $expenseModel)
    {
        $this->entityManager->persist($expenseModel);
        $this->entityManager->flush($expenseModel);
    }

    public function findById($id): ExpenseModel|null
    {
        return $this
            ->entityManager
            ->find(ExpenseModel::class, $id);
    }

    public function getAll(): array
    {
        return $this
            ->entityManager
            ->getRepository(ExpenseModel::class)
            ->findAll();
    }

}
