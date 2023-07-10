<?php

namespace App\service;

use App\model\TransactionModel;
use App\model\UserModel;
use Doctrine\ORM;

class TransactionService extends Service {

    /**
     * Save model to database
     * @param TransactionModel $transaction
     * @return void
     */
    public function save(TransactionModel $transaction) {
        $this->entityManager->persist($transaction);
        $this->entityManager->flush($transaction);
    }

    /**
     * Retrieve transaction from db base on id.
     * @param int $id
     * @return TransactionModel
     */
    public function findById($id): TransactionModel {
        $em = $this->entityManager;
        $transaction = $em->find(TransactionModel::class, $id);
        return $transaction;
    }

    /**
     * Retrieve user transaction from db
     * @param UserModel $user 
     * @param int $page current page
     * @param int $max max transaction per page
     * @return array An array containing 'transactions' and 'totalTransactions'.
     */
    public function findAll($user,$page,$max) {

        $result = [];

        // Step 1: Define pagination settings
        $transactionsPerPage = $max;
        $currentPage = $page; // Set the current page based on user input or any other criteria

        $em = $this->entityManager;

        // Step 3: Fetch paginated transactions
        $queryBuilder =  $em->createQueryBuilder();
        $queryBuilder->select('t')
            ->from(TransactionModel::class, 't')
            ->where('t.user = :user')
            ->setParameter('user', $user)
            ->setMaxResults($transactionsPerPage)
            ->setFirstResult(($currentPage - 1) * $transactionsPerPage);


        // Step 4: Execute the query and retrieve transactions
        $result['transactions'] = $queryBuilder->getQuery()->getResult();

        $queryBuilder =  $em->createQueryBuilder();
        $queryBuilder->select('count(t.id)')
            ->from(TransactionModel::class, 't')
            ->where('t.user = :user')
            ->setParameter('user', $user);

       $result['totalTransaction'] = $queryBuilder->getQuery()->getSingleScalarResult();

        return $result;
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
