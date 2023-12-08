<?php

namespace App\service;

use App\model\budget\FundModel;
use App\model\ReceiptModel;

class FundService extends Service
{

    public function save(FundModel $fundModel)
    {
        $this->entityManager->persist($fundModel);
        $this->entityManager->flush($fundModel);
    }

    public function findById($id): FundModel|null
    {
        return $this
            ->entityManager
            ->find(FundModel::class, $id);
    }

    public function getAll(): array
    {
        return $this
            ->entityManager
            ->getRepository(FundModel::class)
            ->findBy(['isArchived' => false]);
    }

}
