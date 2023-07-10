<?php

namespace App\service;

use App\lib\Time;
use App\model\TransactionLogsModel;
use App\model\UserModel;
use Doctrine\ORM;

class TransactionLogsService extends Service {

    /**
     * Save model to database
     * @param TransactionLogsModel $transaction
     * @return void
     */
    public function save(TransactionLogsModel $transaction) {
        $this->entityManager->persist($transaction);
        $this->entityManager->flush($transaction);
    }

    public function log($transaction,$user,$message,$action){
        $logs = new TransactionLogsModel();
        $logs->setMessage($message);
        $logs->setCreated_at(Time::timestamp());
        $logs->setTransaction($transaction);
        $logs->setUpdatedBy($user);
        $logs->setAction($action);

        $this->save($logs);
    }

    /**
     * Retrieve transaction from db base on id.
     * @param int $id
     * @return TransactionLogsModel
     */
    public function findById($id): TransactionLogsModel {
        $em = $this->entityManager;
        $transaction = $em->find(TransactionLogsModel::class, $id);
        return $transaction;
    }

}
