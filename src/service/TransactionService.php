<?php

namespace App\service;

use App\exception\payment\TransactionNotFound;
use App\lib\Paginator;
use App\lib\Time;
use App\model\PaymentModel;
use App\model\TransactionModel;
use App\model\UserModel;
use DateTime;

class TransactionService extends Service
{

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
     * @throws TransactionNotFound
     */
    public function findById($id): TransactionModel
    {
        $em = $this->entityManager;
        $transaction = $em->find(TransactionModel::class, $id);

        if (!isset($transaction)) {
            throw new TransactionNotFound($id);
        }

        return $transaction;
    }

    public function getPayments($page = 1, $max = 5, $id = null, $status = null, $from = null, $to = null)
    {

        $qb = $this->entityManager->createQueryBuilder();

        $qb->select('t')
            ->from(TransactionModel::class, 't');

        $or = $qb->expr()->andX();

        $paginator = new Paginator();

        $hasQuery = false;

        if (isset($user)) {
            $qb->innerJoin('t.user', 'u', 'WITH', 'u.block = :block AND u.lot = :lot')
                ->setParameter('block', $user->getBlock())
                ->setParameter('lot', $user->getLot());
            $hasQuery = true;
        }

        if (isset($id)) {
            $or->add($qb->expr()->eq('t.id', ':id'));
            $qb->setParameter('id', $id);
            $hasQuery = true;
        }

        if (isset($from)) {
            $or->add($qb->expr()->gte('t.fromMonth', ':from'));
            $qb->setParameter('from', (new \DateTime($from))->format('Y-m-d'));
            $hasQuery = true;

        }

        if (isset($to)) {
            $or->add($qb->expr()->lte('t.toMonth', ':to'));

            $endOfMonth = (new \DateTime($to))->modify('last day of this month')->format('Y-m-d');

            $qb->setParameter('to', $endOfMonth);
            $hasQuery = true;
        }

        if (isset($status)) {
            $or->add($qb->expr()->eq('t.status', ':status'));
            $qb->setParameter('status', $status);
            $hasQuery = true;

        }

        if ($hasQuery) {
            $qb->where($or);
        }

        return $paginator->paginate($qb, $page, $max);
    }

    public function getUserPayments(UserModel $user, $page = 1, $max = 5, $id = null, $status = null, $from = null, $to = null)
    {

        $qb = $this->entityManager->createQueryBuilder();

        $qb->select('t')
            ->from(TransactionModel::class, 't')
            ->innerJoin('t.user', 'u', 'WITH', 't.user  = u.id')
            ->orderBy('t.id','DESC');

        $or = $qb->expr()->andX();

        $or->add($qb->expr()->eq('t.user',':user'));
        $qb->setParameter('user',$user);

        $paginator = new Paginator();

        $hasQuery = false;

        if (isset($id)) {
            $or->add($qb->expr()->eq('t.id', ':id'));
            $qb->setParameter('id', $id);
            $hasQuery = true;
        }

        if (isset($from)) {
            $or->add($qb->expr()->gte('t.fromMonth', ':from'));
            $qb->setParameter('from', (new \DateTime($from))->format('Y-m-d'));
            $hasQuery = true;

        }

        if (isset($to)) {
            $or->add($qb->expr()->lte('t.toMonth', ':to'));

            $endOfMonth = (new \DateTime($to))->modify('last day of this month')->format('Y-m-d');

            $qb->setParameter('to', $endOfMonth);
            $hasQuery = true;
        }

        if (isset($status)) {
            $or->add($qb->expr()->eq('t.status', ':status'));
            $qb->setParameter('status', $status);
            $hasQuery = true;

        }

            $qb->where($or);

        return $paginator->paginate($qb, $page, $max);
    }

    public function getUnpaid($user, DuesService $dueService, PaymentModel $payment, $startMonth = null, $endMonth = null): array
    {

        $months = [];

        if ($startMonth != null and $endMonth != null) {
            $months = Time::getMonths($startMonth, $endMonth);
        } else {
            $months = Time::getMonths(Time::convertToString($payment->getStart()), Time::thisMonth());
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
     * Otherwise, balance is the due for the month.
     * @param UserModel $user
     * @param string $month
     * @param DuesService @dueService
     * @return float
     */
    public function getBalance($user, $month, DuesService $dueService): float
    {
        return $this->isPaid($user, $month) ? 0 : $dueService->getDue($month);
    }


    /**
     * Check if user paid for a certain month
     * @param Month month to check
     * @return bool values
     */
    public function isPaid($user, $month)
    {

        $em = $this->entityManager;

        $qb = $em->createQueryBuilder();

        $count = $qb->select('COUNT(t.id)')
            ->from(TransactionModel::class, 't')
            ->innerJoin('t.user', 'u', 'WITH', 'u.block = :block AND u.lot = :lot')
            ->setParameter('block', $user->getBlock())
            ->setParameter('lot', $user->getLot())
            ->where($qb->expr()->andX(
                $qb->expr()->between(':month', 't.fromMonth', 't.toMonth'),
                $qb->expr()->eq('t.status', ':status'))
            )
            ->setParameter('month', $month)
            ->setParameter('status', 'APPROVED')
            ->getQuery()
            ->getSingleScalarResult();

        return $count > 0;
    }


    /**
     * Returns total collected amount from transactions
     * base on the status provided
     * @param string $status
     * @param string $fromMonth
     * @param string $toMonth
     * @return float
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function getTotal(string $status, string $fromMonth, string $toMonth): float
    {

        $qb = $this->entityManager->createQueryBuilder();

        $result = $qb->select('sum(t.amount)')
            ->from(TransactionModel::class, 't')
            ->where(
                $qb->expr()->andX(
                    $qb->expr()->eq('t.status', ':status'),
                    $qb->expr()->gte('t.fromMonth', ':fromMonth'),
                    $qb->expr()->lte('t.toMonth', ':toMonth'),
                )
            )
            ->setParameter('fromMonth', $fromMonth)
            ->setParameter('toMonth', $toMonth)
            ->setParameter('status', $status)
            ->getQuery()
            ->getSingleScalarResult();

        return $result ?? 0;
    }

    public function getTransactions(string $from, string $to, string $status = 'approved', string $block = null, string $lot = null): array
    {

        $fromDate = DateTime::createFromFormat('Y-m', $from);
        $toDate = DateTime::createFromFormat('Y-m', $to);

        $fromMonth = (int)$fromDate->format('m');
        $toMonth = (int)$toDate->format('m');

        $fromYear = (int)$fromDate->format('Y');
        $toYear = (int)$toDate->format('Y');

        $qb = $this->entityManager->createQueryBuilder();

        $andX = $qb->expr()->andX(
            $qb->expr()->eq('t.status', ':status'),
            $qb->expr()->gte('MONTH(t.fromMonth)', ':fromMonth'),
            $qb->expr()->lte('MONTH(t.toMonth)', ':toMonth'),
            $qb->expr()->gte('YEAR(t.fromMonth)', ':fromYear'),
            $qb->expr()->lte('YEAR(t.toMonth)', ':toYear'));

        if (isset($block) && $block !== 'ALL') {
            $andX->add($qb->expr()->eq('u.block', ':block'));
            $qb->setParameter('block', $block);
        }

        if (isset($lot) && $lot !== 'ALL') {
            $andX->add($qb->expr()->eq('u.lot', ':lot'));
            $qb->setParameter('lot', $lot);
        }

        return $qb->select('t')
            ->from(TransactionModel::class, 't')
            ->innerJoin('t.user', 'u', 'WITH', 't.user = u.id')
            ->where($andX)
            ->setParameter('status', $status)
            ->setParameter('fromMonth', $fromMonth)
            ->setParameter('toMonth', $toMonth)
            ->setParameter('fromYear', $fromYear)
            ->setParameter('toYear', $toYear)
            ->getQuery()
            ->getResult();
    }

    public function getExpectedCollection($user, DuesService $dueService, PaymentModel $payment, $startMonth, $endMonth): array
    {

        $months = $months = Time::getMonths($startMonth, $endMonth);

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

}
