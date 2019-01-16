<?php

namespace App\Models;

use Dibi\Connection;
use Dibi\DriverException;
use Dibi\Fluent;
use Nette\Caching\Cache;
use Nette\Caching\IStorage;
use Nette\Utils\ArrayHash;
use SplFileInfo;

/**
 * Manager wraps dibi connection to database
 *
 * @author rendix2
 * @package App\Models
 */
abstract class Manager extends Tables
{
    /**
     * @var string
     */
    const CACHE_TABLES = 'tables';

    /**
     *
     * @var Connection $dibi dibi
     */
    protected $dibi;

    /**
     * @var Cache $cache
     */
    protected $managerCache;

    /**
     * @var IStorage $storage
     */
    protected $storage;

    /**
     * @var string $table
     */
    protected $table;

    /**
     * @var string $primaryKey
     */
    private $primaryKey;

    /**
     *
     * @var array $columnNames
     */
    private $columnNames;

    /**
     * Manager constructor.
     *
     * @param Connection $dibi
     * @param IStorage   $storage
     *
     * @throws DriverException
     */
    public function __construct(Connection $dibi, IStorage $storage)
    {
        $table = $this->getNameOfTableFromClass();

        $databaseCache = new Cache($storage, $this->dibi->getDatabaseInfo()->getName());
        $cachedTables  = $databaseCache->load(self::CACHE_TABLES);

        if ($cachedTables === null) {
            $cachedTables = $this->dibi->getDatabaseInfo()->getTableNames();
            $databaseCache->save(self::CACHE_TABLES, $cachedTables);
        }

        if (!in_array($table, $cachedTables, true)) {
            $message = sprintf(
                "Table '%s' does not exist in database '%s'.",
                $table,
                $this->dibi->getDatabaseInfo()->getName()
            );

            throw new DriverException($message);
        }

        $this->dibi         = $dibi;
        $this->storage      = $storage;
        $this->table        = $table;
        $this->managerCache = new Cache($storage, $this->getTable());
        $this->primaryKey   = $this->getPrimaryKeyQuery();
        $this->columnNames  = $this->getColumnsQuery();
    }
    
    /**
     * Manager destructor.
     */
    public function __destruct()
    {
        if ($this->dibi && $this->dibi->isConnected()) {
            $this->dibi->disconnect();
        }
                
        $this->dibi         = null;
        $this->table        = null;
        $this->primaryKey   = null;
        $this->managerCache = null;
        $this->storage      = null;
        $this->columnNames  = null;
    }

    /**
     * @return string
     * @see https://stackoverflow.com/questions/1993721/how-to-convert-camelcase-to-camel-case
     */
    private function getNameOfTableFromClass()
    {
        $className    = str_replace('Manager', '', get_class($this));
        $explodedName = explode('\\', $className);
        $count        = count($explodedName);

        return mb_strtolower(preg_replace('/(?<!^)[A-Z]/', '_$0', $explodedName[$count - 1]));
    }

    /**
     * @return string
     */
    private function getPrimaryKeyQuery()
    {
        $cachedPrimaryKey = $this->managerCache->load('primaryKey_' . $this->table);

        if ($cachedPrimaryKey === null) {
            $columns = $this->dibi->getDatabaseInfo()->getTable($this->table)->getColumns();

            foreach ($columns as $column) {
                if ($column->getVendorInfo('Key') === 'PRI') {
                    $cachedPrimaryKey = $column->name;
                    break;
                }
            }

            $this->managerCache->save(
                'primaryKey_' . $this->table,
                $cachedPrimaryKey,
                [
                    Cache::EXPIRE => '168 hours',
                ]
            );
        }

        return $cachedPrimaryKey;
    }

    /**
     * @return string
     */
    private function getColumnsQuery()
    {
        $cachedColumns = $this->managerCache->load('columns_' . $this->table);

        if ($cachedColumns === null) {
            // runs query!!!!! we need cache
            $cachedColumns = $this->dibi->getDatabaseInfo()->getTable($this->table)->columnNames;

            $this->managerCache->save(
                'columns_' . $this->table,
                $cachedColumns,
                [Cache::EXPIRE => '168 hours',]
            );
        }

        return $cachedColumns;
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
    public function getTable()
    {
        return $this->table;
    }

    /**
     * @return Fluent
     */
    public function getAllFluent()
    {
        return $this->dibi
            ->select('*')
            ->from($this->table);
    }

    /**
     * @return Fluent
     */
    public function getCountFluent()
    {
        return $this->dibi
            ->select('COUNT(*)')
            ->from($this->table);
    }

    /**
     * @return Fluent
     */
    public function getCountPrimaryKeyFluent()
    {
        return $this->dibi
            ->select('COUNT('.$this->primaryKey.')')
            ->from($this->table);
    }

    /**
     * @param ArrayHash $item_data
     *
     * @return Fluent
     */
    public function updateFluent(ArrayHash $item_data)
    {
        $this->deleteCache();

        return $this->dibi
            ->update($this->table, $item_data);
    }

    /**
     * @return Fluent
     */
    public function deleteFluent()
    {
        $this->deleteCache();

        return $this->dibi->delete($this->getTable());
    }

    /**
     * @param int|array|null $item_id
     */
    protected function deleteCache($item_id = null)
    {
        $this->managerCache->remove(self::CACHE_ALL_KEY);
        $this->managerCache->remove(self::CACHE_PAIRS);
        $this->managerCache->remove(self::CACHE_COUNT_KEY);

        if ($item_id) {
            if (is_array($item_id)) {
                foreach ($item_id as $item) {
                    $this->managerCache->remove(self::CACHE_ONE . '_' . $item);
                }
            } else {
                $this->managerCache->remove(self::CACHE_ONE . '_' . $item_id);
            }
        }
    }

    /**
     * returns extension of file
     *
     * @param string $fileName file name
     *
     * @return string
     * @api
     */
    public static function getFileExtension($fileName)
    {
        $file = new SplFileInfo($fileName);

        return $file->getExtension();
    }

    /**
     * this method returns random string
     *
     * @return string
     * @see    https://php.vrana.cz/trvale-prihlaseni.php php vrana
     */
    public static function getRandomString()
    {
        return mb_substr(md5(uniqid(mt_rand(), true)), 0, 15); // php.vrana.cz
    }
}
