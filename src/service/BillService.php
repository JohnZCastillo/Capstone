<?php

namespace App\service;

use App\exception\fund\BillNotFound;
use App\model\budget\BillModel;

class BillService extends Service
{

    public function save(BillModel $billModel)
    {
        $this->entityManager->persist($billModel);
        $this->entityManager->flush($billModel);
    }

    /**
     * @throws BillNotFound
     */
    public function findById($id): BillModel
    {
        $bill = $this
            ->entityManager
            ->find(BillModel::class, $id);

        if(!isset($bill)){
            throw new BillNotFound("Bill with id of $id is missing");
        }

        return $bill;
    }

    public function getAll(bool $archived = false): array
    {
        return $this
            ->entityManager
            ->getRepository(BillModel::class)
            ->findBy(['isArchived' => $archived]);
    }

}
