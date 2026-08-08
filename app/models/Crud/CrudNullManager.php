<?php

namespace App\Models\Crud;

use Dibi\Connection;
use Dibi\Fluent;
use Dibi\Row;
use Nette\Caching\IStorage;
use Nette\Utils\ArrayHash;

/**
 * Description of CrudNullManager
 *
 * @author rendix2
 * @package App\Models\Crud
 */
#[\JetBrains\PhpStorm\Deprecated]

class CrudNullManager extends CrudManager
{
    /**
     * @var Connection $connection
     */
    private $connection;

    /**
     * CrudNullManager constructor.
     *
     * @param Connection $dibi
     * @param IStorage   $storage
     */
    public function __construct(Connection $dibi, IStorage $storage)
    {
        $this->connection = $dibi;
    }
    


    /**
     * @return Fluent
     */
    public function getAllFluent()
    {
        return $this->connection->select('1');
    }


    /**
     * @param int $item_id
     *
     * @return Row|false|void
     */
    public function getById($item_id)
    {
    }


    /**
     * @return string
     */
    public function getPrimaryKey()
    {
    }

    /**
     * @return string
     */
    public function getTable()
    {
    }

    /**
     * @param ArrayHash $item_data
     *
     * @return void
     */
    public function add(ArrayHash $item_data)
    {
    }

    /**
     * @param int $item_id
     *
     * @return void
     */
    public function delete($item_id)
    {
    }

    /**
     * @param int|null $item_id
     */
    public function deleteCache($item_id = null)
    {
    }


    /**
     * @param int       $item_id
     * @param ArrayHash $item_data
     *
     * @return void
     */
    public function update($item_id, ArrayHash $item_data)
    {
    }

}
