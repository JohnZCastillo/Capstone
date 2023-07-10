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

    /**
     * Retrieve transaction from db base on id.
     * @param int id
     * @return TransactionModel
     */
    public function findById($id): TransactionModel {
        $em = $this->entityManager;
        $transaction = $em->find(UserModel::class, $id);
        return $transaction;
    }

    /**
     * Check if user paid for a certain month
     * @param Month month to check
     * @return bool values
     */
    public function isPaid($month) {

        // em - Entity Manager
        // eq - Query Builder
        // lte - Least than expression
        // gte - Greather than expression
        
        $em = $this->entityManager;
        $qb = $em->createQueryBuilder();
    
        $qb->select('COUNT(u.id)')
            ->from(TransactionModel::class, 'u') 
            ->where($qb->expr()->between(':month', 'u.fromMonth', 'u.toMonth'))
            ->andWhere($qb->expr()->eq('u.status', ':status'))
            ->setParameter('month', $month)
            ->setParameter('status', 'APPROVED');
    
        $query = $qb->getQuery();
        $count = $query->getSingleScalarResult();
    
        // Returns t// Returns true if the user has paid for the specified month, false otherwise
        return ($count > 0); 
    }
}
