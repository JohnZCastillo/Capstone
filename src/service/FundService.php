<?php

namespace App\service;

use App\model\budget\FundModel;

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

    public function getAll(bool $archived = false): array
    {
        return $this
            ->entityManager
            ->getRepository(FundModel::class)
            ->findBy(['isArchived' => $archived]);
    }

}
