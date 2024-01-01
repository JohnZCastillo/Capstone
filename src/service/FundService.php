<?php

namespace App\service;

use App\exception\fund\FundNotFound;
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

    /**
     * @throws FundNotFound
     */
    public function findById($id): FundModel
    {
        $fund = $this
            ->entityManager
            ->find(FundModel::class, $id);

        if (!isset($fund)) {
            throw new FundNotFound("Fund with id of $id not found");
        }
        return $fund;
    }

    public function getAll(bool $archived = false): array
    {
        return $this
            ->entityManager
            ->getRepository(FundModel::class)
            ->findBy(['isArchived' => $archived]);
    }

    public function getMonthlyExpenses($fundId, int $month, int $year): float
    {
        $expenseQb = $this->entityManager->createQueryBuilder();

        $expense = $expenseQb->select('SUM(e.amount)')
            ->from(ExpenseModel::class, 'e')
            ->where(
                $expenseQb->expr()->andX(
                    $expenseQb->expr()->eq('e.fund', ':fundId'),
                    $expenseQb->expr()->eq('MONTH(e.createdAt)', ':startMonth'),
                    $expenseQb->expr()->lte('YEAR(e.createdAt)', ':endYear'),
                    $expenseQb->expr()->eq('e.status', ':status')
                )
            ) ->setParameter('fundId', $fundId)
            ->setParameter('startMonth', $month)
            ->setParameter('endYear', $year)
            ->setParameter('status', BudgetStatus::approved())
            ->getQuery()
            ->getSingleScalarResult();

        return  $expense ?? 0;
    }

    public function getMonthlyIncomes($fundId, int $month, int $year): float
    {

        $incomeQb = $this->entityManager->createQueryBuilder();

        $income = $incomeQb->select('SUM(i.amount)')
            ->from(IncomeModel::class, 'i')
            ->where(
                $incomeQb->expr()->andX(
                    $incomeQb->expr()->eq('i.fund', ':fundId'),
                    $incomeQb->expr()->eq('MONTH(i.createdAt)', ':startMonth'),
                    $incomeQb->expr()->lte('YEAR(i.createdAt)', ':endYear')
                )
            ) ->setParameter('fundId', $fundId)
            ->setParameter('startMonth', $month)
            ->setParameter('endYear', $year)
            ->getQuery()
            ->getSingleScalarResult();

        return  $income ?? 0;
    }

    public function getYearlyIncome(int $fundId, int $year): array
    {

        $tally = [];

        foreach (Time::getDatesForMonthsOfYear($year) as $month) {

            $currentMonth = (int) $month->format('m');

            $tally[$month->format('M')] = $this->getMonthlyIncomes($fundId, $currentMonth,$year);
        }

        return $tally;


    }

    public function getYearlyExpenses(int $fundId, int $year): array
    {

        $tally = [];

        foreach (Time::getDatesForMonthsOfYear($year) as $month) {

            $currentMonth = (int) $month->format('m');


            $tally[$month->format('M')] = $this->getMonthlyExpenses($fundId, $currentMonth,$year);
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

    public function getCollection(int $fundId, int $year): array
    {

        $tally = [];

        foreach (Time::getDatesForMonthsOfYear($year) as $month) {

            $currentMonth = (int) $month->format('m');

            $tally[$month->format('M')] = $this->getMonthlyFund($fundId, $currentMonth,$year);
        }

        return $tally;
    }

    public function getMonthlyFund(int $fundId, int $month, int $year): float
    {
        return self::getMonthlyIncomes($fundId,$month,$year) - self::getMonthlyExpenses($fundId,$month,$year) ;
    }

}
