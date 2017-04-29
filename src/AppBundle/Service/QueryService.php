<?php
/**
 * @author Artyom Bochkarev
 */

namespace AppBundle\Service;

use AppBundle\Model\QueryParams;
use Elastica\Query;
use Elastica\ResultSet;
use Elastica\Type;

class QueryService
{
    /**
     * @var Type
     */
    private $repository;

    /**
     * @param Type $repository
     */
    public function __construct(Type $repository)
    {
        $this->repository = $repository;
    }

    /**
     * @param Query $query
     *
     * @return ResultSet
     */
    public function query($query)
    {
        return $this->repository->search($query);
    }

    /**
     * @param QueryParams $queryParams
     *
     * @return Query
     */
    public function buildQuery(QueryParams $queryParams)
    {
        $query     = new Query();
        $boolQuery = new Query\BoolQuery();
        $this->applyFilters($queryParams, $boolQuery);

        $baseQuery = $this->getBaseQuery($queryParams);
        $boolQuery->addFilter($baseQuery);
        $query->setQuery($boolQuery);

        $this->applySorting($query, $queryParams);

        return $query;
    }

    /**
     * @param QueryParams $queryParams
     *
     * @return Query\AbstractQuery|Query\MatchAll
     */
    private function getBaseQuery($queryParams)
    {
        $searchQuery = $queryParams->getSearchQuery();
        if ($searchQuery) {
            $baseQuery = $this->getSearchQuery($queryParams);
        } elseif ('' === $searchQuery) {
            $baseQuery = new Query\Match();
            $baseQuery->setField('_id', '-1');
        } else {
            $baseQuery = new Query\MatchAll();
        }

        return $baseQuery;
    }

    /**
     * @param QueryParams $queryParams
     *
     * @return Query\AbstractQuery
     */
    private function getSearchQuery(QueryParams $queryParams)
    {
        $queryString = $queryParams->getSearchQuery();

        $fields = [
            'title^3',
        ];

        $query = new Query\MultiMatch();

        return $query
                ->setQuery($queryString)
                ->setFields($fields)
                ->setTieBreaker(0.3)
                ->setOperator('and')
                ->setParam('fuzziness', '1')
                ->setParam('lenient', true)
        ;
    }

    /**
     * @param QueryParams     $queryParams
     * @param Query\BoolQuery $boolQuery
     */
    private function applyFilters(QueryParams $queryParams, Query\BoolQuery $boolQuery)
    {
        if ($queryParams->getFilterId()) {
            $this->applyIdFilter($queryParams, $boolQuery);
        }
    }

    /**
     * @param Query       $query
     * @param QueryParams $queryParams
     */
    private function applySorting(Query $query, QueryParams $queryParams)
    {
        if ($queryParams->getSearchQuery()) {
            $query->addSort(['_score' => 'desc', 'id' => 'desc']);
        } else {
            $query->addSort(['id' => 'desc']);
        }
    }

    /**
     * @param QueryParams     $queryParams
     * @param Query\BoolQuery $query
     */
    private function applyIdFilter(QueryParams $queryParams, Query\BoolQuery $query)
    {
        $productId    = $queryParams->getFilterId();
        $queryTerm = is_array($productId) ?
            new Query\Terms('id', $productId) :
            new Query\Term(['id' => $productId])
        ;

        $query->addMust($queryTerm);
    }
}
