<?php

namespace App\service;

use App\model\PaymentModel;
use Doctrine\ORM\EntityManager;

class PaymentService extends Service {

    /**
     * Save model to database
     * @param PaymentModel $receipt 
     * @return void
     */
    
    public function save(PaymentModel $receipt) {
        $this->entityManager->persist($receipt);
        $this->entityManager->flush($receipt);
    }


    public function findById($id){
        $em = $this->entityManager;
        $receipt = $em->find(PaymentModel::class, $id);

        return $receipt;
    }

}
