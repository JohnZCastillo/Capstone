<?php

namespace App\service;

use App\model\budget\FundModel;
use App\model\budget\FundSourceModel;

class FundSourceService extends Service
{

    public function save(FundSourceModel $fundSourceModel)
    {
        $this->entityManager->persist($fundSourceModel);
        $this->entityManager->flush($fundSourceModel);
    }

    public function findById($id): FundSourceModel|null
    {
        return $this
            ->entityManager
            ->find(FundSourceModel::class, $id);
    }

    public function getAll(bool $archived = false): array
    {
        return $this
            ->entityManager
            ->getRepository(FundSourceModel::class)
            ->findBy(['isArchived' => $archived]);
    }

}
