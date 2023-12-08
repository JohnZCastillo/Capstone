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

}
