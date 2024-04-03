<?php

namespace App\service;

use App\model\budget\ProjectExpenseModel;
use App\model\budget\ProjectExpenseProofModel;
use App\model\budget\ProjectModel;

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

    public function  getProjects(): array
    {
        $qb = $this->entityManager->createQueryBuilder();

        return $qb->select('p')
            ->from(ProjectModel::class, 'p')
            ->getQuery()
            ->getResult();

    }


}
