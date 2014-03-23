<?php
/**
 *
 * @package spec\DG37\Repository
 * @author  Nerijus Eimanavičius <nerijus@eimanavicius.lt>
 */

namespace DG37\Repository;

use DG37\Entity\Stockist;

/**
 * Class StockistRepositoryInterface
 * @package spec\DG37\Repository
 */
interface StockistRepositoryInterface
{
    /**
     * @param string $name
     *
     * @return Stockist|null
     */
    public function findOneByName($name);

    /**
     * @return array
     */
    public function findAll();

    /**
     * @param Stockist $stockist
     *
     * @return void
     */
    public function save(Stockist $stockist);
}
