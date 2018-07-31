<?php

namespace App\Models\Crud;

use App\Models\Manager;
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
use Exception;

/**
 * Description of CrudManager
 *
 * @author rendi
 */
abstract class CrudManager extends Manager //implements ICrudManager
{
    /**
     * @var string
     */
    const CACHE_ALL_KEY = 'data';

    /**
     * @var string
     */
    const CACHE_COUNT_KEY = 'count';

    /**
     * @var string
     */
    const CACHE_PAIRS = 'pairs';

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
    protected $managerCache;
    
    /**
     * @var IStorage $storage
     */
    protected $storage;
    
    /**
     *
     * @var array $columnNames
     */
    private $columnNames;

    /**
     * CrudManager constructor.
     *
     * @param Connection $dibi
     * @param IStorage   $storage
     * 
     * @throws Exception
     */
    public function __construct(Connection $dibi, IStorage $storage)
    {
        parent::__construct($dibi);

        $this->storage = $storage;
        
        $table = $this->getNameOfTableFromClass();
        
        $databaseCache = new Cache($storage, $this->dibi->getDatabaseInfo()->getName());
        $cachedTables  = $databaseCache->load('tables');
                     
        if ($cachedTables === null) {
            $cachedTables = $this->dibi->getDatabaseInfo()->getTableNames();
            $databaseCache->save('tables', $cachedTables);
        }
        
        if (!in_array($table, $cachedTables)) {
            throw new Exception("Table {$table} does not exists in database {$this->dibi->getDatabaseInfo()->getName()}");
        }
                
        $this->table        = $table;
        $this->managerCache = new Cache($storage, $this->getTable());
        $this->primaryKey   = $this->getPrimaryKeyQuery();
        $this->columnNames  = $this->getColumnsQuery();
    }
    
    /**
     * 
     * @param string $column
     * @param string $value
     * 
     * @return type
     * @throws Exception
     */
    public function get($column, $value)
    {
        if (!in_array($column, $this->columnNames)) {
            throw new Exception("Non existing {$column} column in table {$this->table}");
        }
        
        $type = $this->getColumnTypeQuery($column);
        
        $query = $this->dibi->select('*')
                ->from($this->table);
        
        if ($type === 'TEXT' || $type === 'VARCHAR') {
            $query = $query->where('%n = %s', $column, $value);
        } else if ($type === 'INT') {
            $query = $query->where('%n = %i', $column, $value);
        } else if (is_array($value)) {
            $query = $query->where('%n IN %in', $column, $value);
        } else if ($type === 'ENUM') {
            $query = $query->where('%n = %s', $column, $value);
        }
        
       return $query;
    }

    /**
     * @return array
     */
    public function getAll()
    {
        return $this->dibi
            ->select('*')
            ->from($this->table)
            ->fetchAll();
    }

    /**
     * @return array|mixed
     */
    public function getAllCached()
    {
        $cached = $this->managerCache->load(self::CACHE_ALL_KEY);

        if ($cached === null) {
            $this->managerCache->save(
                self::CACHE_ALL_KEY,
                $cached = $this->getAll(),
                [Cache::EXPIRE => '24 hours']
            );
        }

        return $cached;
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
     * @param string $second
     *
     * @return array
     */
    public function getAllPairs($second)
    {
        return $this->dibi
            ->select($this->getPrimaryKey() . ', ' . $second)
            ->from($this->getTable())
            ->fetchPairs($this->getPrimaryKey(), $second);
    }

    /**
     * @param string $second
     *
     * @return array|mixed
     */
    public function getAllPairsCached($second)
    {
        $cached = $this->managerCache->load(self::CACHE_PAIRS);

        if ($cached === null) {
            $this->managerCache->save(
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
        return $this->dibi
            ->select('*')
            ->from($this->table)
            ->where('%n = %i', $this->primaryKey, $item_id)
            ->fetch();
    }

    /**
     * @param array $item_id
     *
     * @return Row[]
     */
    public function getByIds(array $item_id)
    {
        return $this->dibi
            ->select('*')
            ->from($this->table)
            ->where('%n IN %in', $this->primaryKey, $item_id)
            ->fetchAll();
    }

    /**
     * @return int
     */
    public function getCount()
    {
        return $this->dibi
            ->select('COUNT(*)')
            ->from($this->table)
            ->fetchSingle();
    }

    /**
     * @return int
     */
    public function getCountCached()
    {
        $cached = $this->managerCache->load(self::CACHE_COUNT_KEY);

        if ($cached === null) {
            $this->managerCache->save(
                self::CACHE_COUNT_KEY,
                $cached = $this->getCount(),
                [Cache::EXPIRE => '24 hours']
            );
        }

        return $cached;
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
    public function getPrimaryKey()
    {
        return $this->primaryKey;
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
                if ($column->getVendorInfo('Key') === 'PRI')
                {
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
                [
                    Cache::EXPIRE => '168 hours',
                ]
            );
        }

        return $cachedColumns;
    }
    
    private function getColumnTypeQuery($column)
    {
        $cachedColumn = $this->managerCache->load('Columns_objects_' . $this->table. '_column_'.$column);
        
        if ( $cachedColumn === null) {
            $cachedColumn = $this->dibi->getDatabaseInfo()->getTable($this->table)->getColumn($column)->getNativeType();            
            
            $this->managerCache->save(
                'Columns_objects_' . $this->table. '_column_'.$column,
                $cachedColumn,
                [
                    Cache::EXPIRE => '168 hours',
                ]
            );
        }
        
        return $cachedColumn;
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
        return $this->dibi
            ->insert($this->table, $item_data)
            ->execute(dibi::IDENTIFIER);
    }

    /**
     * @param int $item_id
     *
     * @return Result|int
     */
    public function delete($item_id)
    {
        return $this->dibi
            ->delete($this->table)
            ->where('%n = %i', $this->primaryKey, $item_id)
            ->execute();
    }

    /**
     *
     */
    public function deleteCache()
    {
        $this->managerCache->remove(self::CACHE_ALL_KEY);
    }

    /**
     * @param array $item_id
     *
     * @return Result|int
     */
    public function deleteMulti(array $item_id)
    {
        return $this->dibi
            ->delete($this->table)
            ->where('[%n IN %in', $this->primaryKey, $item_id)
            ->execute(dibi::AFFECTED_ROWS);
    }

    /**
     * @param int       $item_id
     * @param ArrayHash $item_data
     *
     * @return Result|int
     */
    public function update($item_id, ArrayHash $item_data)
    {
        return $this->dibi
                ->update($this->table, $item_data)
                ->where('%n = %i', $this->primaryKey, $item_id)
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
        return $this->dibi
            ->update($this->table, $item_data)
            ->where('%n IN %in', $this->primaryKey, $item_id)
            ->execute(dibi::AFFECTED_ROWS);
    }
}
