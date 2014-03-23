<?php

namespace DG37\Repository;

use DG37\Entity\Stockist;

class InMemoryStockistRepository implements StockistRepositoryInterface
{

    /**
     * @var array
     */
    protected $stockists = [];

    /**
     * @param Stockist $stockist
     */
    public function save(Stockist $stockist)
    {
        $this->stockists[$stockist->getName()] = $stockist;
    }

    /**
     * @param string $name
     *
     * @return Stockist|null
     */
    public function findOneByName($name)
    {
        if (isset($this->stockists[$name])) {
            return $this->stockists[$name];
        }
        return null;
    }

    /**
     * @return array
     */
    public function findAll()
    {
        return array_values($this->stockists);
    }
}
