<?php

namespace App\service;

use App\model\AreaModel;
use App\model\UserModel;
use Doctrine\ORM\NonUniqueResultException;

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

    public function exist(string $block, string $lot): bool
    {

        $qb = $this->entityManager->createQueryBuilder();

        $query = $qb->select('count(e)')
            ->from(AreaModel::class, 'e')
            ->where($qb->expr()->andX(
                $qb->expr()->eq('e.block', ':block'),
                $qb->expr()->eq('e.lot', ':lot')
            ))
            ->setParameter('block', $block)
            ->setParameter('lot', $lot);

        return $query->getQuery()->getSingleScalarResult() > 0;
    }

    public function getOwner(UserModel $userModel): string
    {

        $qb = $this->entityManager->createQueryBuilder();

        $result = $qb->select('a.owner')
            ->from(AreaModel::class, 'a')
            ->where($qb->expr()->andX(
                $qb->expr()->eq('a.block', ':block'),
                $qb->expr()->eq('a.lot', ':lot')
            ))
            ->setParameter('block', $userModel->getBlock())
            ->setParameter('lot', $userModel->getLot())
            ->getQuery()->getSingleScalarResult();

        return $result ?? 'John Doe';
    }

    public function updateOwner(UserModel $userModel): void
    {

        $qb = $this->entityManager->createQueryBuilder();

        $result = $qb->update(AreaModel::class,'a')
            ->set('a.owner',':owner')
            ->where($qb->expr()->andX(
                $qb->expr()->eq('e.block', ':block'),
                $qb->expr()->eq('e.lot', ':lot')
            ))
            ->setParameter('owner', $userModel->getName())
            ->setParameter('block', $userModel->getBlock())
            ->setParameter('lot', $userModel->getLot())
            ->getQuery()
            ->execute();

    }
}
