<?php
/**
 * @author Artyom Bochkarev
 */

namespace AppBundle\Model;

class QueryParams
{
    /**
     * @var int|array
     */
    private $filterId;

    /**
     * @var int
     */
    private $size;

    /**
     * @var string
     */
    private $searchQuery;

    /**
     * Initialize fields
     */
    public function __construct()
    {
        $this->size        = 1;
        $this->searchQuery = null;
    }

    /**
     * @return int|array
     */
    public function getFilterId()
    {
        return $this->filterId;
    }

    /**
     * @param int|array $filterId
     *
     * @return QueryParams
     */
    public function setFilterId($filterId)
    {
        $this->filterId = $filterId;

        return $this;
    }

    /**
     * @return string
     */
    public function getSearchQuery()
    {
        return $this->searchQuery;
    }

    /**
     * @param string $searchQuery
     *
     * @return QueryParams
     */
    public function setSearchQuery($searchQuery)
    {
        $this->searchQuery = $searchQuery;

        return $this;
    }

    /**
     * @return int
     */
    public function getSize()
    {
        return $this->size;
    }

    /**
     * @param int $size
     *
     * @return QueryParams
     */
    public function setSize($size)
    {
        $this->size = $size;

        return $this;
    }
}
