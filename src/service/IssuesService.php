<?php

namespace App\service;

use App\lib\Helper;
use App\model\IssuesModel;

// use App\model\IssuesModel;
// use App\model\UserModel;
// use Doctrine\ORM\EntityManager;

class IssuesService extends Service {

    /**
     * Save model to database
     * @param IssuesModel $dues 
     * @return void
     */
    public function save(IssuesModel $dues) {
        $this->entityManager->persist($dues);
        $this->entityManager->flush($dues);
    }

    public function findById($id): IssuesModel {
        $em = $this->entityManager;
        $dues = $em->find(IssuesModel::class, $id);
        return $dues;
    }

    public function getAll($page, $max, $id,$filter, $user = null) {

        $result = [];

        // Step 1: Define pagination settings
        $transactionsPerPage = $max;
        $currentPage = $page; // Set the current page based on user input or any other criteria

        $em = $this->entityManager;

        // Step 3: Fetch paginated issues
        $queryBuilder = $em->createQueryBuilder();
        $queryBuilder->select('t')
            ->from(IssuesModel::class, 't');

        if(Helper::existAndNotNull($user)){
            $queryBuilder->where('t.user = :user')
            ->setParameter('user', $user);
        }

        if (Helper::existAndNotNull($id)) {
            $queryBuilder->andWhere('t.id like :id')->setParameter('id', $id);
        }

        if (Helper::existAndNotNull($filter,'from')) {
            $queryBuilder->andWhere('t.fromMonth >= :from')->setParameter('from', $filter['from']);
        }

        if (Helper::existAndNotNull($filter,'to')) {
            $queryBuilder->andWhere('t.toMonth <= :to')->setParameter('to', $filter['to']);
        }

        if (Helper::existAndNotNull($filter,'status')) {
            if($filter['status'] != 'ALL'){
                $queryBuilder->andWhere('t.status = :status')->setParameter('status', $filter['status']);
            }
        }

        $queryBuilder->setMaxResults($transactionsPerPage)
            ->setFirstResult(($currentPage - 1) * $transactionsPerPage);

        // Step 4: Execute the query and retrieve issues
        $issues = $queryBuilder->getQuery()->getResult();

        $queryBuilder = $em->createQueryBuilder();
        $queryBuilder->select('count(t.id)')
            ->from(IssuesModel::class, 't');

            if(Helper::existAndNotNull($user)){
                $queryBuilder->where('t.user = :user')
                ->setParameter('user', $user);
            }
    
            if (Helper::existAndNotNull($id)) {
                $queryBuilder->andWhere('t.id like :id')->setParameter('id', $id);
            }
    
            if (Helper::existAndNotNull($filter,'from')) {
                $queryBuilder->andWhere('t.fromMonth >= :from')->setParameter('from', $filter['from']);
            }
    
            if (Helper::existAndNotNull($filter,'to')) {
                $queryBuilder->andWhere('t.toMonth <= :to')->setParameter('to', $filter['to']);
            }
    
            if (Helper::existAndNotNull($filter,'status')) {
                if($filter['status'] != 'ALL'){
                    $queryBuilder->andWhere('t.status = :status')->setParameter('status', $filter['status']);
                }
            }

        $totaIssues = $queryBuilder->getQuery()->getSingleScalarResult();

        return [
            'issues' => $issues,
            'totalIssues' => $totaIssues
        ];
    }

}
