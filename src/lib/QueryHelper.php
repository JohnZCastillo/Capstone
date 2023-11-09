<?php

namespace App\lib;

use Doctrine\ORM\QueryBuilder;

/**
 * Class QueryHelper
 *
 * A helper class to dynamically build and modify Doctrine ORM queries.
 */
class QueryHelper {

    /**
     * @var int Keeps track of whether the query has already been modified.
     */
    private int $called;

    /**
     * @var QueryBuilder The QueryBuilder instance to work with.
     */
    private QueryBuilder $query;

    /**
     * QueryHelper constructor.
     *
     * @param QueryBuilder $query The QueryBuilder instance to use for query building.
     */
    public function __construct(QueryBuilder $query)
    {
        // Initialize the 'called' counter to 0, indicating no modifications yet.
        $this->called = 0;
        $this->query = $query;
    }

    /**
     * Adds an AND WHERE condition to the query if the value is set.
     *
     * @param string $andWhereExpression The AND WHERE expression to add.
     * @param string $setParameterExpression The parameter name for setParameter method.
     * @param mixed $value The value to set for the parameter.
     * @return QueryHelper Returns the QueryHelper instance for method chaining.
     */
    public function andWhere(string $andWhereExpression, string $setParameterExpression, $value): QueryHelper
    {
        $query = $this->query;

        $called = $this->called;

        if ($called <= 0) {
            // If this is the first modification, treat it as a regular 'where'.
            return $this->where($andWhereExpression, $setParameterExpression, $value);
        } else {
            if (isset($value)) {
                // Add an AND WHERE condition and set the parameter.
                $query->andWhere($andWhereExpression)
                    ->setParameter($setParameterExpression, $value);
            }
        }

        return $this;
    }

    public function andWhereIn(string $andWhereExpression, $value1,$value2): QueryHelper
    {
        $query = $this->query;

        $called = $this->called;

        if ($called <= 0) {
            if ( isset($value1['value'],$value2['value'])) {
                // If this is the first modification, treat it as a regular 'where'.
                $query->where($andWhereExpression)
                    ->setParameter($value1['key'],$value1['value'])
                    ->setParameter($value2['key'],$value2['value']);

                      $this->alreadyCalled();
            }

        } else {
            if (isset($value1['value'],$value2['value']) ) {
                // Add an AND WHERE condition and set the parameter.
                $query->andWhere($andWhereExpression)
                    ->setParameter($value1['key'],$value1['value'])
                    ->setParameter($value2['key'],$value2['value']);
            }
        }

        return $this;
    }


    /**
     * Adds a WHERE condition to the query and sets a parameter value.
     *
     * @param string $whereExpression The WHERE expression to add.
     * @param string $setParameterExpression The parameter name for setParameter method.
     * @param mixed $value The value to set for the parameter.
     * @return QueryHelper Returns the QueryHelper instance for method chaining.
     */
    public function where($whereExpression, $setParameterExpression, $value): QueryHelper
    {
        $query = $this->query;

        if (isset($value)) {
            // Add a WHERE condition and set the parameter.
            $query->where($whereExpression)
                ->setParameter($setParameterExpression, $value);

            // Mark the query as modified.
            $this->alreadyCalled();
        }

        return $this;
    }

    /**
     * Marks the query as already modified.
     */
    private function alreadyCalled(): void
    {
        $this->called = 1;
    }

    /**
     * Retrieves the QueryBuilder instance.
     *
     * @return QueryBuilder The QueryBuilder instance.
     */
    public function getQuery(): QueryBuilder
    {
        return $this->query;
    }
}