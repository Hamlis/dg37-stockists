<?php

namespace spec\DG37\Repository;

use DG37\Entity\Stockist;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class InMemoryStockistRepositorySpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldBeAnInstanceOf('DG37\Repository\InMemoryStockistRepository');
    }

    function it_is_a_stockist_repository()
    {
        $this->shouldBeAnInstanceOf('DG37\Repository\StockistRepositoryInterface');
    }

    function it_stores_stockists(Stockist $stockist1, Stockist $stockist2)
    {
        $stockist1->getName()->willReturn('Stockist #1');
        $stockist2->getName()->willReturn('Stockist #2');

        $this->save($stockist1);
        $this->save($stockist2);

        $this->findAll()->shouldReturn([$stockist1, $stockist2]);
        $this->findOneByName('Stockist #1')->shouldReturn($stockist1);
    }

    function it_returns_null_if_stockist_not_found()
    {
        $this->findOneByName('Do not exists')->shouldReturn(null);
    }
}
