<?php

namespace DG37\Repository;

use DG37\Entity\Stockist;
use PDO;

class StockistRepository implements StockistRepositoryInterface
{

    /**
     * @var PDO
     */
    protected $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    /**
     * @param string $name
     *
     * @return Stockist|null
     */
    public function findOneByName($name)
    {
        $sth = $this->pdo->prepare('SELECT * FROM stockists WHERE name = ?');
        $sth->execute([$name]);
        $data = $sth->fetch(PDO::FETCH_ASSOC);
        return is_array($data) ? $this->arrayToStockist($data) : null;
    }

    /**
     * @return array
     */
    public function findAll()
    {
        $sth = $this->pdo->query('SELECT * FROM stockists ORDER BY name ASC');
        $stockists = $sth->fetchAll(PDO::FETCH_ASSOC);
        return array_map(array($this, 'arrayToStockist'), $stockists);
    }

    /**
     * @param array $data
     *
     * @return Stockist
     */
    protected function arrayToStockist(array $data)
    {
        $stockist = new Stockist($data['name']);
        foreach ($data as $key => $value) {
            $stockist->{'set' . ucfirst($key)}($value);
        }
        return $stockist;
    }

    /**
     * @param Stockist $stockist
     *
     * @return void
     */
    public function save(Stockist $stockist)
    {
        $this->pdo->beginTransaction();
        $this->pdo->prepare("INSERT OR IGNORE INTO stockists (name) VALUES (?)")
            ->execute([$stockist->getName()]);
        $sth = $this->pdo->prepare(
            "
            UPDATE stockists
              SET address1 = ?, address2 = ?, city = ?, state = ?, postcode = ?, phone = ?, url = ?, image = ?,
                latitude = ?, longitude = ?
              WHERE name = ?
            "
        );
        $fields = [
            'address1',
            'address2',
            'city',
            'state',
            'postcode',
            'phone',
            'url',
            'image',
            'latitude',
            'longitude',
            'name',
        ];
        $stockistData = [];
        foreach ($fields as $field) {
            $stockistData[] = $stockist->{'get' . ucfirst($field)}();
        }
        $sth->execute($stockistData);
        $this->pdo->commit();
    }

    /**
     * @param array $names
     */
    public function removeOthers(array $names)
    {
        $placeHolders = implode(',', array_fill(0, count($names), '?'));
        $sth = $this->pdo->prepare("DELETE FROM stockists WHERE name NOT IN ({$placeHolders})");
        $sth->execute($names);
    }
}
