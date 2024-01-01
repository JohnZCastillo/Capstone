<?php

namespace App\service;

use App\model\AreaModel;
use App\model\budget\BillModel;

class AreaService extends Service
{

    public function getBlock(): array
    {
        $qb = $this->entityManager->createQueryBuilder();

        return $qb->select('e.block')
            ->distinct()
            ->from(AreaModel::class, 'e')
            ->getQuery()
            ->getSingleColumnResult();
    }

    public function getLot($block): array
    {
        $qb = $this->entityManager->createQueryBuilder();

        return $qb->select('e.lot')
            ->from(AreaModel::class, 'e')
            ->where(
                $qb->expr()->andX(
                    $qb->expr()->eq('e.block', ':block')
                )
            )
            ->setParameter('block', $block)
            ->getQuery()
            ->getSingleColumnResult();
    }

}
