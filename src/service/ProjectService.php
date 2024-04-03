<?php

namespace App\service;

use App\model\budget\ProjectExpenseModel;
use App\model\budget\ProjectExpenseProofModel;
use App\model\budget\ProjectModel;
use App\model\ReceiptModel;
use App\model\TransactionModel;

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



}
