<?php

namespace App\Models\Crud;

namespace App\Models\Crud;

use App\Models\Manager;
use App\Presenters\crud\CrudPresenter;
use dibi;
use Dibi\Connection;
use Dibi\Fluent;
use Dibi\Result;
use Dibi\Row;
use Nette\Caching\Cache;
use Nette\Caching\IStorage;
use Nette\Utils\ArrayHash;
use Tracy\Debugger;
use Tracy\ILogger;

/**
 * Description of CrudNullManager
 *
 * @author rendi
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
     */
    public function __construct(Connection $dibi)
    {
        $this->connection = $dibi;
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
    private function getNameOfTableFromClass()
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
    private function getPrimaryKeyQuery()
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
     * @return Result|int
     */
    public function add(ArrayHash $item_data)
    {
    }

    /**
     * @param int $item_id
     *
     * @return Result|int
     */
    public function delete($item_id)
    {
    }

    /**
     *
     */
    public function deleteCache()
    {
    }

    /**
     * @param array $item_id
     *
     * @return Result|int
     */
    public function deleteMulti(array $item_id)
    {
    }


    /**
     * @param int       $item_id
     * @param ArrayHash $item_data
     *
     * @return Result|int
     */
    public function update($item_id, ArrayHash $item_data)
    {
    }

    /**
     * @param array     $item_id
     * @param ArrayHash $item_data
     *
     * @return Result|int
     */
    public function updateMulti(array $item_id, ArrayHash $item_data)
    {
    }
}
