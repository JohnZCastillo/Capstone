<?php

namespace App\service;

use App\exception\fund\FundSourceNotFound;
use App\model\budget\FundModel;
use App\model\budget\FundSourceModel;
use Doctrine\ORM\Exception\NotSupported;

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

    /**
     * @throws FundSourceNotFound
     */
    public function findByName(string $name): FundSourceModel
    {
        $fundSource =  $this
            ->entityManager
            ->getRepository(FundSourceModel::class)
            ->findOneBy(['name' => $name]);

        if(!isset($fundSource)){
            throw new FundSourceNotFound("Fund with name of $name is missing");
        }

        return $fundSource;
    }

}
