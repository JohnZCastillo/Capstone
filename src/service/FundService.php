<?php

namespace App\service;

use App\lib\Time;
use App\model\budget\ExpenseModel;
use App\model\budget\FundModel;
use App\model\budget\IncomeModel;
use App\model\enum\BudgetStatus;

class FundService extends Service
{

    public function save(FundModel $fundModel)
    {
        $this->entityManager->persist($fundModel);
        $this->entityManager->flush($fundModel);
    }

    public function findById($id): FundModel|null
    {
        return $this
            ->entityManager
            ->find(FundModel::class, $id);
    }

    public function getAll(bool $archived = false): array
    {
        return $this
            ->entityManager
            ->getRepository(FundModel::class)
            ->findBy(['isArchived' => $archived]);
    }

    /**
     * Assuming $month is the specific month you want to filter for (e.g., '2023-07')
     * @param $fundId
     * @param $month
     * @return float
     * @throws \Exception
     */
    public function getMonthlyExpenses($fundId, $month): float
    {
        $qb = $this->entityManager->createQueryBuilder();

        $startDate = new \DateTime($month);
        $endDate = (clone $startDate)->modify('last day of this month')->setTime(23, 59, 59);

        $result = $qb->select('sum(e.amount)')
            ->from(ExpenseModel::class, 'e')
            ->join('e.fund', 'f', 'WITH', 'e.fund = f.id',)
            ->where('f.id = :fundId')
            ->andWhere(
                $qb->expr()->andX(
                    $qb->expr()->eq('f.id', ':fundId'),
                    $qb->expr()->eq('e.status', ':status'),
                    $qb->expr()->between('e.createdAt', ':startDate', ':endDate')
                )
            )
            ->setParameter('fundId', $fundId)
            ->setParameter('startDate', $startDate)
            ->setParameter('endDate', $endDate)
            ->setParameter('status', BudgetStatus::approved())
            ->getQuery()
            ->getSingleScalarResult();

        if (!isset($result)) {
            return -1;
        }

        return $result;
    }

    /**
     * Assuming $month is the specific month you want to filter for (e.g., '2023-07')
     * @param $fundId
     * @param $month
     * @return float
     * @throws \Exception
     */
    public function getMonthlyIncomes($fundId, $month): float
    {
        $qb = $this->entityManager->createQueryBuilder();

        $startDate = new \DateTime($month);
        $endDate = (clone $startDate)->modify('last day of this month')->setTime(23, 59, 59);

        $result = $qb->select('sum(e.amount)')
            ->from(IncomeModel::class, 'e')
            ->join('e.fund', 'f', 'WITH', 'e.fund = f.id',)
            ->where('f.id = :fundId')
            ->andWhere(
                $qb->expr()->andX(
                    $qb->expr()->eq('f.id', ':fundId'),
                    $qb->expr()->between('e.createdAt', ':startDate', ':endDate')
                )
            )
            ->setParameter('fundId', $fundId)
            ->setParameter('startDate', $startDate)
            ->setParameter('endDate', $endDate)
            ->getQuery()
            ->getSingleScalarResult();

        if (!isset($result)) {
            return -1;
        }

        return $result;
    }

    public function getMonthlyTally(int $fundId, int $year): array
    {


        $tally = [];

        $totalIncome = 0;

        foreach (Time::getDatesForMonthsOfYear($year) as $month) {

            $currentMonth = $month->format('Y-m');

            $totalIncome += $this->getMonthlyIncomes($fundId, $currentMonth);
            $totalIncome -= $this->getMonthlyExpenses($fundId, $currentMonth);

            $tally[$month->format('M')] = $totalIncome;
        }

        return $tally;
    }

    public function getYearlyIncome(int $fundId, int $year): array
    {

        $tally = [];

        foreach (Time::getDatesForMonthsOfYear($year) as $month) {

            $currentMonth = $month->format('Y-m');

            $tally[$month->format('M')] = $this->getMonthlyIncomes($fundId, $currentMonth);
        }

        return $tally;
    }

    public function getYearlyExpenses(int $fundId, int $year): array
    {

        $tally = [];

        foreach (Time::getDatesForMonthsOfYear($year) as $month) {

            $currentMonth = $month->format('Y-m');

            $tally[$month->format('M')] = $this->getMonthlyExpenses($fundId, $currentMonth);
        }

        return $tally;
    }

    public function getKeys(int $year): array
    {

        $tally = [];

        foreach (Time::getDatesForMonthsOfYear($year) as $month) {

            $tally[] = $month->format('M');
        }

        return $tally;
    }

}
