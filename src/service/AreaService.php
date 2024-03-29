<?php

namespace App\service;

use App\model\AreaModel;

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

    public function getArea(string $block, string $lot): array
    {

        if ($lot === 'ALL') {
            $lot = null;
        }

        if ($block === 'ALL') {
            $block = null;
        }

        $qb = $this->entityManager->createQueryBuilder();

        $andX = $qb->expr()->andX();

        $query = $qb->select('e')
            ->from(AreaModel::class, 'e');

        if (isset($block)) {
            $andX->add($qb->expr()->eq('e.block', ':block'));
            $qb->setParameter('block', $block);

        }

        if (isset($lot)) {
            $andX->add($qb->expr()->eq('e.lot', ':lot'));
            $qb->setParameter('lot', $lot);
        }

        if (isset($block) || isset($lot)) {
            $qb->where($andX);
        }

        return $query->getQuery()
            ->getResult();
    }

}
