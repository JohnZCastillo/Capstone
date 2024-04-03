<?php

namespace App\service;

use App\model\budget\ProjectExpenseModel;
use App\model\budget\ProjectExpenseProofModel;
use App\model\budget\ProjectModel;
use App\model\enum\ProjectType;

class ProjectService extends Service {

    public function saveProject(ProjectModel $receipt) {
        $this->entityManager->persist($receipt);
        $this->entityManager->flush($receipt);
    }

    public function saveProjectExpense(ProjectExpenseModel $receipt) {
        $this->entityManager->persist($receipt);
        $this->entityManager->flush($receipt);
    }

    public function saveProjectExpenseProof(ProjectExpenseProofModel $receipt) {
        $this->entityManager->persist($receipt);
        $this->entityManager->flush($receipt);
    }

    public function  getProjects(string $type = ProjectType::ACTIVE): array
    {
        $qb = $this->entityManager->createQueryBuilder();

        return $qb->select('p')
            ->from(ProjectModel::class, 'p')
            ->where($qb->expr()->eq('p.type',':type'))
            ->setParameter('type',$type)
            ->getQuery()
            ->getResult();

    }

    public function getProjectById($id): ProjectModel
    {
        $qb = $this->entityManager->createQueryBuilder();

        return $qb->select('p')
            ->from(ProjectModel::class, 'p')
            ->where($qb->expr()->eq('p.id',':id'))
            ->setParameter('id',$id)
            ->getQuery()
            ->getSingleResult();
    }

    public function count(string $status): int
    {
        $qb = $this->entityManager->createQueryBuilder();

        $count = $qb->select('COUNT(p)')
            ->from(ProjectModel::class, 'p')
            ->where($qb->expr()->eq('p.status',':status'))
            ->setParameter('status',$status)
            ->getQuery()
            ->getSingleScalarResult();

        return  $count ?? 0;
    }


}
