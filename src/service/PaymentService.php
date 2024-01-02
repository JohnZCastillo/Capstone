<?php

namespace App\service;

use App\exception\payment\PaymentNotFound;
use App\model\PaymentModel;

class PaymentService extends Service
{

    /**
     * Save model to database
     * @param PaymentModel $receipt
     * @return void
     */

    public function save(PaymentModel $receipt)
    {
        $this->entityManager->persist($receipt);
        $this->entityManager->flush($receipt);
    }


    /**
     * Return a payment by its id
     * @params id
     * @throws PaymentNotFound
     */
    public function findById($id): PaymentModel
    {

        $em = $this->entityManager;

        $paymentModel = $em->find(PaymentModel::class, $id);

        if ($paymentModel == null) {
            throw new PaymentNotFound($id);
        }

        return $paymentModel;
    }


}
