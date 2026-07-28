<?php

namespace App\Models\Crud;

use App\Models\Manager;
use dibi;
use Dibi\Connection;
use Dibi\DriverException;
use Dibi\Fluent;
use Dibi\Result;
use Dibi\Row;
use Exception;
use InvalidArgumentException;
use Nette\Caching\Cache;
use Nette\Caching\IStorage;
use Nette\Utils\ArrayHash;

/**
 * CrudManager provides create, read, update and delete operations in database table
 *
 * @author rendix2
 * @package App\Models\Crud
 */
#[\JetBrains\PhpStorm\Deprecated]
abstract class CrudManager extends Manager //implements ICrudManager
{
    /**
     *
     * @param string       $column
     * @param string|array $value
     *
     * @return Fluent
     * @throws Exception
     */
    /*
    public function get($column, $value)
    {
        if (!in_array($column, $this->columnNames, true)) {
            throw new Exception("Non existing {$column} column in table {$this->table}");
        }
        
        $type = $this->getColumnTypeQuery($column);

        $query = $this->dibi
            ->select('*')
            ->from($this->table);
        
        if ($type === 'TEXT' || $type === 'VARCHAR') {
            $query = $query->where('%n = %s', $column, $value);
        } elseif ($type === 'INT') {
            $query = $query->where('%n = %i', $column, $value);
        } elseif (is_array($value)) {
            $query = $query->where('%n IN %in', $column, $value);
        } elseif ($type === 'ENUM') {
            $query = $query->where('%n = %s', $column, $value);
        }

        return $query;
    }
    */
    
    /**
     * @return Row[]
     */
    public function getAll()
    {
        return $this->getAllFluent()
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
     * @param int $item_id
     *
     * @return Row|false
     * @throws InvalidArgumentException
     */
    public function getById($item_id)
    {
        if (!is_numeric($item_id)) {
            throw new InvalidArgumentException('Not numeric argument.');
        }
        
        return $this->getAllFluent()
            ->where('%n = %i', $this->getPrimaryKey(), $item_id)
            ->fetch();
    }

    /**
     * @param ArrayHash $item_data
     *
     * @return Result|int
     */
    public function add(ArrayHash $item_data)
    {
        $this->deleteCache();

        return $this->dibi
            ->insert($this->getTable(), $item_data)
            ->execute(dibi::IDENTIFIER);
    }

    /**
     * @param int $item_id
     *
     * @return Result|int
     * @throws InvalidArgumentException
     */
    public function delete($item_id)
    {
        if (!is_numeric($item_id)) {
            throw new InvalidArgumentException('Not numeric argument');
        }
        
        return $this->deleteFluent()
            ->where('%n = %i', $this->getPrimaryKey(), $item_id)
            ->execute();
    }


    /**
     * @param int $item_id
     * @param ArrayHash $item_data
     *
     * @return Result|int
     * @throws InvalidArgumentException
     */
    public function update($item_id, ArrayHash $item_data)
    {
        if (!is_numeric($item_id)) {
            throw new InvalidArgumentException('Not numeric argument');
        }
        
        $this->deleteCache($item_id);
        
        return $this->updateFluent($item_data)
            ->where('%n = %i', $this->getPrimaryKey(), $item_id)
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
        $this->deleteCache($item_id);
        
        return $this->updateFluent($item_data)
            ->where('%n IN %in', $this->getPrimaryKey(), $item_id)
            ->execute(dibi::AFFECTED_ROWS);
    }
}
