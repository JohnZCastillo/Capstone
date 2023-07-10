<?php

namespace App\service;

use App\lib\Time;
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
    public function findAll($user, $page, $max, $id) {

        $result = [];

        // Step 1: Define pagination settings
        $transactionsPerPage = $max;
        $currentPage = $page; // Set the current page based on user input or any other criteria

        $em = $this->entityManager;

        // Step 3: Fetch paginated transactions
        $queryBuilder = $em->createQueryBuilder();
        $queryBuilder->select('t')
            ->from(TransactionModel::class, 't')
            ->where('t.user = :user')
            ->setParameter('user', $user);

        if ($id != null) {
            $queryBuilder->andWhere('t.id like :id')->setParameter('id', $id);
        }

        $queryBuilder->setMaxResults($transactionsPerPage)
            ->setFirstResult(($currentPage - 1) * $transactionsPerPage);

        // Step 4: Execute the query and retrieve transactions
        $transactions = $queryBuilder->getQuery()->getResult();

        $queryBuilder = $em->createQueryBuilder();
        $queryBuilder->select('count(t.id)')
            ->from(TransactionModel::class, 't')
            ->where('t.user = :user')
            ->setParameter('user', $user);

        if ($id != null) {
            $queryBuilder->andWhere('t.id like :id')->setParameter('id', $id);
        }

        $totalTransaction = $queryBuilder->getQuery()->getSingleScalarResult();

        return [
            'transactions' => $transactions,
            'totalTransaction' => $totalTransaction
        ];
    }

       /**
     * Retrieve user transaction from db
     * @param int $page current page
     * @param int $max max transaction per page
     * @return array An array containing 'transactions' and 'totalTransactions'.
     */
    public function getAll($page, $max, $id,$filter) {

        $result = [];

        // Step 1: Define pagination settings
        $transactionsPerPage = $max;
        $currentPage = $page; // Set the current page based on user input or any other criteria

        $em = $this->entityManager;

        // Step 3: Fetch paginated transactions
        $queryBuilder = $em->createQueryBuilder();
        $queryBuilder->select('t')
            ->from(TransactionModel::class, 't');

        if ($id != null) {
            $queryBuilder->andWhere('t.id like :id')->setParameter('id', $id);
        }

        if ($filter['from'] != null) {
            $queryBuilder->andWhere('t.fromMonth = :from')->setParameter('from', $filter['from']);
        }

        if ($filter['to'] != null) {
            $queryBuilder->andWhere('t.toMonth <= :to')->setParameter('to', $filter['to']);
        }

        if ($filter['status'] != null && $filter['status'] != 'ALL') {
            $queryBuilder->andWhere('t.status = :status')->setParameter('status', $filter['status']);
        }

        $queryBuilder->setMaxResults($transactionsPerPage)
            ->setFirstResult(($currentPage - 1) * $transactionsPerPage);

        // Step 4: Execute the query and retrieve transactions
        $transactions = $queryBuilder->getQuery()->getResult();

        $queryBuilder = $em->createQueryBuilder();
        $queryBuilder->select('count(t.id)')
            ->from(TransactionModel::class, 't');

        if ($id != null) {
            $queryBuilder->andWhere('t.id like :id')->setParameter('id', $id);
        }

        if ($filter['from'] != null) {
            $queryBuilder->andWhere('t.fromMonth = :from')->setParameter('from', $filter['from']);
        }

        if ($filter['to'] != null) {
            $queryBuilder->andWhere('t.toMonth <= :to')->setParameter('to', $filter['to']);
        }

        if ($filter['status'] != null && $filter['status'] != 'ALL') {
            $queryBuilder->andWhere('t.status = :status')->setParameter('status', $filter['status']);
        }

        $totalTransaction = $queryBuilder->getQuery()->getSingleScalarResult();

        return [
            'transactions' => $transactions,
            'totalTransaction' => $totalTransaction
        ];
    }

    public function getUnpaid($user, DuesService $dueService){

        $months = Time::getMonths('2023-01-01','2023-12-01');
        
        $data = [];

        $total = 0;

        
        foreach($months as $month){
            
            if($this->isPaid($user,$month)) continue;

            $balance = $this->getBalance($user,$month,$dueService);

            $data[] = [
                'month' => $month,
                'due' => $balance,
            ];
            
            $total += $balance;
        }

        return [
            'items' => $data,
            'total' => $total
        ];
    }

    /**
     * Return the current  balance of user for the certain month.
     * if User has already paid then balance is 0.
     * Otherwise balance is the due for the month.
     * @param UserModel $user
     * @param string $month
     * @param DuesService @dueService
     * @return int
     */
    public function getBalance($user,$month, DuesService $dueService){
        return $this->isPaid($user,$month) ? 0 
        : $dueService->getDue($month);
    }

    
    /**
     * Check if user paid for a certain month
     * @param Month month to check
     * @return bool values
     */
    public function isPaid($user,$month) {

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
            ->andWhere($qb->expr()->eq('u.user', ':user'))
            ->setParameter('month', $month)
            ->setParameter('user', $user)
            ->setParameter('status', 'APPROVED');

        $query = $qb->getQuery();
        $count = $query->getSingleScalarResult();

        // Returns t// Returns true if the user has paid for the specified month, false otherwise
        return ($count > 0);
    }
}
