<?php

namespace App\service;

use App\model\budget\BillModel;

class BillService extends Service
{

    public function save(BillModel $billModel)
    {
        $this->entityManager->persist($billModel);
        $this->entityManager->flush($billModel);
    }

    public function findById($id): BillModel|null
    {
        return $this
            ->entityManager
            ->find(BillModel::class, $id);
    }

    public function getAll(bool $archived = false): array
    {
        return $this
            ->entityManager
            ->getRepository(BillModel::class)
            ->findBy(['isArchived' => $archived]);
    }

}
