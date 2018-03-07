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
 * Description of CrudManager
 *
 * @author rendi
 */
abstract class CrudManager extends Manager
{

    /**
     *
     */
    const CACHE_ALL_KEY = 'data';

    /**
     *
     */
    const CACHE_COUNT_KEY = 'count';

    /**
     *
     */
    const CACHE_PAIRS = 'pairs';

    /**
     * @var IStorage $storage
     */
    public $storage;

    /**
     * @var string $table
     */
    private $table;

    /**
     * @var string $primaryKey
     */
    private $primaryKey;

    /**
     * @var Cache $cache
     */
    private $cache;

    /**
     * CrudManager constructor.
     *
     * @param Connection $dibi
     */
    public function __construct(Connection $dibi)
    {
        parent::__construct($dibi);

        $this->table = $this->getNameOfTableFromClass();
    }

    /**
     * @return array
     */
    public function getAll()
    {
        return $this->dibi->select('*')
            ->from($this->table)
            ->fetchAll();
    }

    /**
     * @return array|mixed
     */
    public function getAllCached()
    {
        $cache = new Cache(
            $this->getStorage(),
            $this->getTable()
        );

        $cached = $cache->load(self::CACHE_ALL_KEY);

        if (!$cached) {
            $cache->save(
                self::CACHE_ALL_KEY,
                $cached = $this->getAll(),
                [
                    Cache::EXPIRE => '24 hours',
                ]
            );
        }

        return $cached;
    }

    /**
     * @return Fluent
     */
    public function getAllFluent()
    {
        return $this->dibi->select('*')
            ->from($this->table);
    }

    /**
     * @param string $second
     *
     * @return array
     */
    public function getAllPairs($second)
    {
        return $this->dibi->select($this->getPrimaryKey() . ', ' . $second)
            ->from($this->getTable())
            ->fetchPairs(
                $this->getPrimaryKey(),
                $second
            );
    }

    /**
     * @param string $second
     *
     * @return array|mixed
     */
    public function getAllPairsCached($second)
    {
        $cache = new Cache(
            $this->getStorage(),
            $this->getTable()
        );

        $cached = $cache->load(self::CACHE_PAIRS);

        if (!$cached) {
            $cache->save(
                self::CACHE_PAIRS,
                $cached = $this->getAllPairs($second),
                [
                    Cache::EXPIRE => '24 hours',
                ]
            );
        }

        return $cached;
    }

    /**
     * @param int $item_id
     *
     * @return Row|false
     */
    public function getById($item_id)
    {
        return $this->dibi->select('*')
            ->from($this->table)
            ->where(
                '[' . $this->primaryKey . '] = %i',
                $item_id
            )
            ->fetch();
    }

    /**
     * @param array $item_id
     *
     * @return array
     */
    public function getByIds(array $item_id)
    {
        return $this->dibi->select('*')
            ->from($this->table)
            ->where(
                '[' . $this->primaryKey . '] IN %in',
                $item_id
            )
            ->fetchAll();
    }

    /**
     * @return Cache
     */
    public function getCache()
    {
        return $this->cache;
    }

    /**
     * @return string
     */
    public function getCount()
    {
        return $this->dibi->select('COUNT(*)')
            ->from($this->table)
            ->fetchSingle();
    }

    /**
     * @return string
     */
    public function getCountCached()
    {
        $cache  = new Cache(
            $this->getStorage(),
            $this->getTable()
        );
        $cached = $cache->load(self::CACHE_COUNT_KEY);

        if (!$cached) {
            $cache->save(
                self::CACHE_COUNT_KEY,
                $cached = $this->getCount(),
                [
                    Cache::EXPIRE => '24 hours',
                ]
            );
        }

        return $cached;
    }

    /**
     * @return string
     */
    private function getNameOfTableFromClass()
    {
        $explodedName = explode(
            '\\',
            str_replace(
                'Manager',
                '',
                get_class($this)
            )
        );
        $count        = count($explodedName);

        return mb_strtolower($explodedName[$count - 1]);
    }

    /**
     * @return string
     */
    public function getPrimaryKey()
    {
        return $this->primaryKey;
    }

    /**
     * @return string
     */
    private function getPrimaryKeyQuery()
    {
        $cachedPrimaryKey = $this->cache->load('primaryKey_' . $this->table);

        if (!$cachedPrimaryKey) {
            $data = $this->dibi->select('COLUMN_NAME')
                ->from('information_schema.COLUMNS')
                ->where(
                    '[TABLE_NAME] = %s',
                    $this->table
                )
                ->where(
                    '[COLUMN_KEY] = %s',
                    'PRI'
                )
                ->fetchSingle();

            if (!$data) {
                Debugger::log(
                    'Primary key of table:' . $this->table . ' was not found!',
                    ILogger::CRITICAL
                );
            }

            $this->cache->save(
                'primaryKey_' . $this->table,
                $cachedPrimaryKey = $data,
                [
                    Cache::EXPIRE => '168 hours',
                ]
            );
        }

        return $cachedPrimaryKey;
    }

    /**
     * @return IStorage
     */
    public function getStorage()
    {
        return $this->storage;
    }

    /**
     * @return string
     */
    public function getTable()
    {
        return $this->table;
    }

    /**
     * @param ArrayHash $item_data
     *
     * @return Result|int
     */
    public function add(ArrayHash $item_data)
    {
        return $this->dibi->insert(
            $this->table,
            $item_data
        )
            ->execute(dibi::IDENTIFIER);
    }

    /**
     * @param int $item_id
     *
     * @return Result|int
     */
    public function delete($item_id)
    {
        return $this->dibi->delete($this->table)
            ->where(
                '[' . $this->primaryKey . '] = %i',
                $item_id
            )
            ->execute(dibi::AFFECTED_ROWS);
    }

    /**
     *
     */
    public function deleteCache()
    {
        $cache = new Cache(
            $this->getStorage(),
            $this->getTable()
        );
        $cache->remove(self::CACHE_ALL_KEY);
    }

    /**
     * @param array $item_id
     *
     * @return Result|int
     */
    public function deleteMulti(array $item_id)
    {
        return $this->dibi->delete($this->table)
            ->where(
                '[' . $this->primaryKey . '] IN %in',
                $item_id
            )
            ->execute(dibi::AFFECTED_ROWS);
    }

    /**
     * @param IStorage $storage
     */
    public function factory(IStorage $storage)
    {
        $this->cache      = new Cache(
            $storage,
            CrudPresenter::CACHE_KEY_PRIMARY_KEY
        );
        $this->primaryKey = $this->getPrimaryKeyQuery();
        $this->storage    = $storage;
    }

    /**
     * @param int       $item_id
     * @param ArrayHash $item_data
     *
     * @return Result|int
     */
    public function update($item_id, ArrayHash $item_data)
    {
        return $this->dibi->update(
            $this->table,
            $item_data
        )
            ->where(
                '[' . $this->primaryKey . '] = %i',
                $item_id
            )
            ->execute(dibi::AFFECTED_ROWS);
    }

    /**
     * @param array     $item_id
     * @param ArrayHash $item_data
     *
     * @return Result|int
     */
    public function updateMulti(array $item_id, ArrayHash $item_data)
    {
        return $this->dibi->update(
            $this->table,
            $item_data
        )
            ->where(
                '[' . $this->primaryKey . '] IN %in',
                $item_id
            )
            ->execute(dibi::AFFECTED_ROWS);
    }
}
