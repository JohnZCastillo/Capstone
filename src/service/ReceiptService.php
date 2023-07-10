<?php

namespace App\service;

use App\model\ReceiptModel;
use App\model\TransactionModel;
use Doctrine\ORM\EntityManager;

class ReceiptService extends Service {

    /**
     * Save model to database
     * @param ReceiptModel $receipt 
     * @return void
     */
    
    public function save(ReceiptModel $receipt) {
        $this->entityManager->persist($receipt);
        $this->entityManager->flush($receipt);
    }


    public function saveAll($receipts,TransactionModel $transaction) {

        foreach($receipts as $imageName){

            $receipt = new ReceiptModel();
            $receipt->setPath($imageName);
            $receipt->setTransaction($transaction);

            $this->save($receipt);
        }

    }


    public function findById($id): ReceiptModel {
        $em = $this->entityManager;
        $receipt = $em->find(ReceiptModel::class, $id);
        return $receipt;
    }

}
