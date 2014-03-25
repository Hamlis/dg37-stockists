<?php

namespace spec\DG37\Repository;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class StockistRepositorySpec extends ObjectBehavior
{

    /**
     * @var \PDO
     */
    protected $pdo;

    function let() {
        $this->pdo = new \PDO('sqlite::memory:');
        $this->pdo->exec(
            "
            CREATE TABLE IF NOT EXISTS stockists
            (
              name CONSTRAINT uniqueName PRIMARY KEY ASC ON CONFLICT IGNORE,
              address1,
              address2,
              city,
              state,
              postcode,
              phone,
              url,
              image,
              latitude,
              longitude
            )
            "
        );
        $this->beConstructedWith($this->pdo);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('DG37\Repository\StockistRepository');
    }
}
