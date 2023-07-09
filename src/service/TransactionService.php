<?php

namespace App\service;

use App\model\TransactionModel;
use App\model\UserModel;
use Doctrine\ORM\EntityManager;

class TransactionService extends Service {

    /**
     * Save model to database
     * @param TransactionModel transaction model
     * @return void
     */
    public function save(TransactionModel $transaction) {
        $this->entityManager->persist($transaction);
        $this->entityManager->flush($transaction);
    }

    public function findById($id):TransactionModel {
        $em = $this->entityManager;
        $transaction = $em->find(UserModel::class,$id);
        return $transaction;
    }
}
