<?php

namespace App\Models\Crud;

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
 */
class CrudNullManager extends CrudManager implements ICrudManager
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
    
    public function __destruct()
    {
        $this->dibi = null;
        
        parent::__destruct();
    }

    /**
     * @return array|void
     */
    public function getAll()
    {
    }

    /**
     * @return array|mixed|void
     */
    public function getAllCached()
    {
    }

    /**
     * @return Fluent
     */
    public function getAllFluent()
    {
        return $this->connection->select('1');
    }

    /**
     * @param string $second
     *
     * @return array|void
     */
    public function getAllPairs($second)
    {
    }

    /**
     * @param string $second
     *
     * @return array|mixed|void
     */
    public function getAllPairsCached($second)
    {
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
     * @param array $item_id
     *
     * @return array|void
     */
    public function getByIds(array $item_id)
    {
    }

    /**
     * @return string|void
     */
    public function getCount()
    {
    }
     
 /**
     * @return string
     */
    public function getCountCached()
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
     *
     */
    public function deleteCache($item_id = null)
    {
    }

    /**
     * @param array $item_id
     *
     * @return void
     */
    public function deleteMulti(array $item_id)
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

    /**
     * @param array     $item_id
     * @param ArrayHash $item_data
     *
     * @return void
     */
    public function updateMulti(array $item_id, ArrayHash $item_data)
    {
    }
}
