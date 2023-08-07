<?php

namespace App\service;

use App\lib\Paginator;
use App\lib\QueryHelper;
use App\lib\Time;
use App\model\PaymentModel;
use App\model\TransactionModel;
use App\model\UserModel;

class TransactionService extends Service {

    /**
     * Save model to database
     * @param TransactionModel $transaction
     * @return void
     */
    public function save(TransactionModel $transaction)
    {
        $this->entityManager->persist($transaction);
        $this->entityManager->flush($transaction);
    }

    /**
     * Retrieve transaction from db base on id.
     * @param int $id
     * @return TransactionModel
     */
    public function findById($id): TransactionModel
    {
        $em = $this->entityManager;
        $transaction = $em->find(TransactionModel::class, $id);
        return $transaction;
    }


    /**
     * Retrieve user transaction from db
     * @param int $page current page
     * @param int $max max transaction per page
     * @return array An array containing 'transactions' and 'totalTransactions'.
     */
    public function getAll($page, $max, $id, $filter, $user = null)
    {

        $filter['status'] = $filter['status'] == 'ALL' ? null : $filter['status'];

        $em = $this->entityManager;

        $paginator = new Paginator();

        $qb = $em->createQueryBuilder();

        $qb->select('t')
            ->from(TransactionModel::class, 't');

        $queryHelper = new QueryHelper($qb);

        $queryHelper->Where("t.user = :user", "user", $user)
            ->andWhere("t.id like :id", "id", $id)
            ->andWhere("t.fromMonth >= :fromMonth", 'fromMonth', $filter['from'])
            ->andWhere("t.toMonth <= :toMonth", "toMonth", $filter['to'])
            ->andWhere("t.status = :status", "status", $filter['status']);

        return $paginator->paginate($queryHelper->getQuery(), $page, $max);
    }

    public function getUnpaid($user, DuesService $dueService, PaymentModel $payment, $startMonth = null, $endMonth = null)
    {

        $months = [];

        if ($startMonth != null and $endMonth != null) {
            $months = Time::getMonths($startMonth, $endMonth);
        } else {
            $months = Time::getMonths($payment->getStart(), Time::thisMonth());
        }

        $data = [];

        $total = 0;

        foreach ($months as $month) {

            if ($this->isPaid($user, $month)) continue;

            $balance = $this->getBalance($user, $month, $dueService);

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
    public function getBalance($user, $month, DuesService $dueService)
    {
        return $this->isPaid($user, $month) ? 0
            : $dueService->getDue($month);
    }


    /**
     * Check if user paid for a certain month
     * @param Month month to check
     * @return bool values
     */
    public function isPaid($user, $month)
    {

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
