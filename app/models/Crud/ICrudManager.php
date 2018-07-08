<?php

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
 * Description of ICrudManager
 *
 * @author rendi
 */
interface ICrudManager
{
    /**
     * ICrudManager constructor.
     *
     * @param Connection $dibi
     */
    public function __construct(Connection $dibi, IStorage $storage);

    /**
     * @return Row[]
     */
    public function getAll();

    /**
     * @return mixed
     */
    public function getAllCached();

    /**
     * @return Fluent
     */
    public function getAllFluent();

    /**
     * @param string $second
     *
     * @return mixed
     */
    public function getAllPairs($second);

    /**
     * @param string $second
     *
     * @return mixed
     */
    public function getAllPairsCached($second);

    /**
     * @param int $item_id
     *
     * @return mixed
     */
    public function getById($item_id);

    /**
     * @param array $item_id
     *
     * @return mixed
     */
    public function getByIds(array $item_id);

    /**
     * @return mixed
     */
    public function getCount();
     
    /**
     * @return string
     */
    public function getCountCached();

    /**
     * @return string
     */
    public function getPrimaryKey();

    /**
     * @return string
     */
    public function getTable();

    /**
     * @param ArrayHash $item_data
     *
     * @return Result|int
     */
    public function add(ArrayHash $item_data);

    /**
     * @param int $item_id
     *
     * @return Result|int
     */
    public function delete($item_id);

    /**
     *
     */
    public function deleteCache();

    /**
     * @param array $item_id
     *
     * @return Result|int
     */
    public function deleteMulti(array $item_id);

    /**
     * @param int       $item_id
     * @param ArrayHash $item_data
     *
     * @return Result|int
     */
    public function update($item_id, ArrayHash $item_data);

    /**
     * @param array     $item_id
     * @param ArrayHash $item_data
     *
     * @return Result|int
     */
    public function updateMulti(array $item_id, ArrayHash $item_data);
}
