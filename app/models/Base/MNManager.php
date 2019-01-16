<?php

namespace App\Models;

use App\Models\Crud\CrudManager;
use Dibi\Connection;
use Dibi\Fluent;
use Dibi\Result;
use Dibi\Row;
use Nette\Caching\IStorage;

/**
 * M2N relation manager.
 *
 * We can do som basic queries we may need.
 * We can fetch all rows by left or key.
 * We may need to join original table or both tables.
 * We can do some counts.
 * We can add.
 * We can merge.
 * We can delete.
 *
 *
 * @author rendix2
 * @package App\Models
 */
abstract class MNManager extends Manager
{
    const ALIAS = 'relation';
    
    /**
     * @var CrudManager $left
     */
    private $left;
    
    /**
     * @var CrudManager $right
     */
    private $right;
    
    /**
     *
     * @var string $leftKey
     */
    private $leftKey;
    
    /**
     *
     * @var string $rightKey
     */
    private $rightKey;
    
    /**
     * 
     * @var string $leftAliasedKey
     */
    private $leftAliasedKey;

    /**
     * 
     * @var string $rightAliasedKey
     */
    private $rightAliasedKey;

    /**
     * MNManager constructor.
     *
     * @param Connection  $dibi
     * @param IStorage    $storage
     * @param CrudManager $left
     * @param CrudManager $right
     * @param string|null $tableName
     * @param null        $leftKey
     * @param null        $rightKey
     */
    public function __construct(
        Connection  $dibi,
        IStorage    $storage,
        CrudManager $left,
        CrudManager $right,
        $tableName = null,
        $leftKey = null,
        $rightKey = null
    ) {
        parent::__construct($dibi, $storage, $tableName);

        $this->left  = $left;
        $this->right = $right;
        
        if ($tableName) {
            $this->table = $tableName;
        } else {
            $this->table = $left->getTable() . '2' . $right->getTable();
        }
        
        if ($leftKey) {
            $this->leftKey = $leftKey;
        } else {
            $this->leftKey = $this->left->getPrimaryKey();
        }
        
        if ($rightKey) {
            $this->rightKey = $rightKey;
        } else {
            $this->rightKey = $this->right->getPrimaryKey();
        }
        
        $this->leftAliasedKey  = self::ALIAS . '.' . $this->leftKey;
        $this->rightAliasedKey = self::ALIAS . '.' . $this->rightKey;
    }

    /**
     * MNManager destructor.
     */
    public function __destruct()
    {
        $this->left            = null;
        $this->right           = null;
        $this->table           = null;
        $this->leftKey         = null;
        $this->rightKey        = null;
        $this->leftAliasedKey  = null;
        $this->rightAliasedKey = null;
                
        parent::__destruct();
    }
    
    /**
     * get all
     */

    /**
     * returns all table
     *
     * @return Row[]
     */
    public function getAll()
    {
        return $this->getAllFluent()
            ->fetchAll();
    }

    /**
     * get by left
     */
    
    /**
     * @param int $left_id
     *
     * @return Fluent
     */
    public function getFluentByLeft($left_id)
    {
        return $this->getAllFluent()
            ->where('%n = %i', $this->leftKey, $left_id);
    }

    /**
     * @param int $left_id
     *
     * @return Row[]
     */
    public function getAllByLeft($left_id)
    {
        return $this->getFluentByLeft($left_id)->fetchAll();
    }
    
    /**
     * @param int $left_id
     *
     * @return Row[]
     */
    public function getPairsByLeft($left_id)
    {
        return $this->getFluentByLeft($left_id)
            ->fetchPairs(null, $this->right->getPrimaryKey());
    }

    /**
     * @param int $left_id
     *
     * @return Fluent
     */
    public function getFluentByLeftJoined($left_id)
    {
        $aliasR = $this->right->getTableAlias();

        return $this->dibi
            ->select($aliasR . '.*')
            ->from($this->table)
            ->as(self::ALIAS)
            ->innerJoin($this->right->getTable())
            ->as($aliasR)
            ->on('%n = %n', $this->right->getAliasedPrimaryKey(), $this->rightAliasedKey)
            ->where('%n = %i', self::ALIAS . '.' . $this->leftKey, $left_id);
    }
    
    /**
     * @param int $left_id
     *
     * @return Row[]
     */
    public function getAllByLeftJoined($left_id)
    {
        return $this->getFluentByLeftJoined($left_id)->fetchAll();
    }
    
    /**
     * get by lefts
     */
    
    /**
     * @param array $left_id
     *
     * @return Fluent
     */
    public function getFluentByLefts(array $left_id)
    {
        return $this->getAllFluent()
            ->where('%n IN %in', $this->leftKey, $left_id);
    }
    
    /**
     * @param array $left_id
     *
     * @return Row[]
     */
    public function getAllByLefts(array $left_id)
    {
        return $this->getFluentByLefts($left_id)->fetchAll();
    }

    /**
     * get by lefts joined
     */
    
    /**
     * @param array $left_id
     *
     * @return Fluent
     */
    public function getFluentByLeftsJoined(array $left_id)
    {
        return $this->dibi
            ->select('*')
            ->from($this->table)
            ->as(self::ALIAS)
            ->innerJoin($this->right->getTable())
            ->as($this->right->getTableAlias())
            ->on('%n = %n', $this->right->getAliasedPrimaryKey(), $this->rightAliasedKey)
            ->where('%n IN %in', $this->leftAliasedKey, $left_id);
    }

    /**
     * @param array $left_id
     *
     * @return Row[]
     */
    public function getAllByLeftsJoined(array $left_id)
    {
        return $this->getFluentByLeftsJoined($left_id)->fetchAll();
    }
    
    /**
     * get by right
     */
    
    /**
     * @param int $right_id
     *
     * @return Fluent
     */
    public function getFluentByRight($right_id)
    {
        return $this->getAllFluent()
            ->where('%n = %i', $this->rightKey, $right_id);
    }

    /**
     * @param int $right_id
     *
     * @return Row[]
     */
    public function getAllByRight($right_id)
    {
        return $this->getFluentByRight($right_id)
            ->fetchAll();
    }
    
    /**
     * @param int $right_id
     *
     * @return Fluent
     */
    public function getPairsByRight($right_id)
    {
        return $this->getAllFluent()
            ->where('%n = %i', $this->rightKey, $right_id);
    }
    
    
    /**
     * get by right joined
     */
    
    /**
     * @param int $right_id
     *
     * @return Fluent
     */
    public function getFluentByRightJoined($right_id)
    {
        $aliasL = $this->left->getTableAlias();

        return $this->dibi->select($aliasL . '.*')
            ->from($this->table)
            ->as(self::ALIAS)
            ->innerJoin($this->left->getTable())
            ->as($aliasL)
            ->on('%n = %n', $this->left->getAliasedPrimaryKey(), $this->leftAliasedKey)
            ->where('%n = %i', $this->rightAliasedKey, $right_id);
    }
    
    /**
     * @param int $right_id
     *
     * @return Row[]
     */
    public function getAllByRightJoined($right_id)
    {
        return $this->getFluentByRightJoined($right_id)
            ->fetchAll();
    }
    
    /**
     * get by rights
     */
    
    /**
     * @param array $right_id
     *
     * @return Fluent
     */
    public function getFluentByRights(array $right_id)
    {
        return $this->getAllFluent()
            ->where('%n IN %in', $this->rightKey, $right_id);
    }
    
    /**
     * @param array $right_id
     *
     * @return Row[]
     */
    public function getAllByRights(array $right_id)
    {
        return $this->getFluentByRights($right_id)
                ->fetchAll();
    }
    
    /**
     * get by rights joined
     */
    
    /**
     * @param array $right_id
     *
     * @return Fluent
     */
    public function getFluentByRightsJoined(array $right_id)
    {
        return $this->dibi->select('*')
            ->from($this->table)
            ->as(self::ALIAS)
            ->innerJoin($this->left->getTable())
            ->as($this->left->getTableAlias())
            ->on('%n = %n', $this->left->getAliasedPrimaryKey(), $this->leftAliasedKey)
            ->where('%n IN %in', $this->rightAliasedKey, $right_id);
    }
    
    /**
     * @param array $right_id
     *
     * @return Row[]
     */
    public function getAllByRightsJoined(array $right_id)
    {
        return $this->getFluentByRightsJoined($right_id)
            ->fetchAll();
    }
    
    /**
     * get all
     */
    
    /**
     * @param int $left_id
     * @param int $right_id
     *
     * @return Fluent
     */
    public function getFluentFull($left_id, $right_id)
    {
        return $this->dibi
            ->select('*')
            ->from($this->table)
            ->as(self::ALIAS)
            ->where('%n = %i', $this->leftAliasedKey, $left_id)
            ->where('%n = %i', $this->rightAliasedKey, $right_id);
    }
    
    /**
     * @param int $left_id
     * @param int $right_id
     *
     * @return Row
     */
    public function getFull($left_id, $right_id)
    {
        return $this->getFluentFull($left_id, $right_id)->fetch();
    }

    /**
     * @param int $left_id
     * @param int $right_id
     *
     * @return Row
     */
    public function getFullJoined($left_id, $right_id)
    {
        $aliasL = $this->left->getTableAlias();
        $aliasR = $this->right->getTableAlias();

        if ($aliasL === $aliasR) {
            $aliasL = $this->left->getTable();
            $aliasR = $this->right->getTable();
        }

        return $this->dibi
            ->select('*')
            ->from($this->table)
            ->as(self::ALIAS)
            ->innerJoin($this->left->getTable())
            ->as($aliasL)
            ->on('%n = %n', $this->left->getAliasedPrimaryKey(), $this->leftAliasedKey)
            ->innerJoin($this->right->getTable())
            ->as($aliasR)
            ->on('%n = %n', $this->right->getAliasedPrimaryKey(), $this->rightAliasedKey)
            ->where('%n = %i', $this->leftAliasedKey, $left_id)
            ->where('%n = %i', $this->rightAliasedKey, $right_id)
            ->fetch();
    }
    
    /**
     * check exists
     */
    
    /**
     * checks if relations exists
     *
     * @param int $left_id
     * @param int $right_id
     *
     * @return bool
     */
    public function fullCheck($left_id, $right_id)
    {
        return $this->dibi
                ->select('1')
                ->from($this->table)
                ->where('%n = %i', $this->leftKey, $left_id)
                ->where('%n = %i', $this->rightKey, $right_id)
                ->fetchSingle() === 1;
    }
    
    /**
     * get counts
     */

    /**
     * @return int
     */
    public function getCount()
    {
        return $this->getCountFluent()
                ->fetchSingle();
    }

    /**
     * @param int $left_id
     *
     * @return int
     */
    public function getCountByLeft($left_id)
    {
        return $this->getCountFluent()
            ->where('%n = %i', $this->leftKey, $left_id)
            ->fetchSingle();
    }

    /**
     * @param int $right_id
     *
     * @return int
     */
    public function getCountByRight($right_id)
    {
        return $this->getCountFluent()
            ->where('%n = %i', $this->rightKey, $right_id)
            ->fetchSingle();
    }
    
    /**
     * add
     */

    /**
     *
     * @param array $values
     *
     * @return Result|int
     */
    public function addNative(array $values)
    {
         return $this->dibi->query('INSERT INTO %n %m', $this->table, $values);
    }
    
    /**
     * @param array    $values
     * @param int|null $left_id
     * @param int|null $right_id
     *
     * @return Result|int|bool
     */
    public function add(array $values, $left_id = null, $right_id = null)
    {
        if (!count($values)) {
            return false;
        }
        
        $data = [];

        foreach ($values as $value) {
            $data[$this->leftKey][]  = $left_id !== null ? (int)$left_id : (int)$value;
            $data[$this->rightKey][] = $right_id !== null ? (int)$right_id : (int)$value;
        }
                
        return $this->addNative($data);
    }


    /**
     * @param int   $left_id
     * @param array $values
     *
     * @return Result|int
     */
    public function addByLeft($left_id, array $values)
    {
        $this->deleteByLeft($left_id);

        return $this->add($values, $left_id, null);
    }
    
    /**
     *
     * @param int   $left_id
     * @param array $values
     */
    public function mergeByLeft($left_id, array $values)
    {
        $left_values = $this->getPairsByLeft($left_id);
        $diff        = array_diff($values, $left_values);
        
        if (count($diff)) {
            $this->add($diff, $left_id, null);
        }
    }

    /**
     * @param int   $right_id
     * @param array $values
     *
     * @return Result|int
     */
    public function addByRight($right_id, array $values)
    {
        $this->deleteByRight($right_id);

        return $this->add($values, null, $right_id);
    }
    
    /**
     *
     * @param int   $right_id
     * @param array $values
     */
    public function mergeByRight($right_id, array $values)
    {
        $right_values = $this->getPairsByRight($right_id);
        $diff         = array_diff($values, $right_values);
        
        if (count($diff)) {
            $this->add($diff, null, $right_id);
        }
    }
    
    /**
     * delete
     */
    
    /**
     *
     * @param int $id
     */
    public function deleteById($id)
    {
        $this->deleteFluent()
             ->where('[id] = %i', $id)
             ->execute();
    }

    /**
     * @param int $left_id
     *
     * @return Result|int
     */
    public function deleteByLeft($left_id)
    {
        return $this->deleteFluent()
            ->where('%n = %i', $this->leftKey, $left_id)
            ->execute();
    }
    
    /**
     * @param int $left_id
     *
     * @return void
     */
    public function deleteFullByLeft($left_id)
    {
        $this->deleteByLeft($left_id);
        $this->left->delete($left_id);
    }
        
    /**
     * @param int $right_id
     *
     * @return Result|int
     */
    public function deleteByRight($right_id)
    {
        return $this->deleteFluent()
            ->where('%n = %i', $this->rightKey, $right_id)
            ->execute();
    }
    
    /**
     * @param int $right_id
     *
     * @return void
     */
    public function deleteFullByRight($right_id)
    {
        $this->deleteByRight($right_id);
        $this->right->delete($right_id);
    }

    /**
     * deletes relation
     *
     * @param int $left_id
     * @param int $right_id
     *
     * @return Result|int
     */
    public function delete($left_id, $right_id)
    {
        return $this->deleteFluent()
            ->where('%n = %i', $this->leftKey, $left_id)
            ->where('%n = %i', $this->rightKey, $right_id)
            ->execute();
    }
    
    /**
     *
     * @param int $left_id
     * @param int $right_id
     */
    public function fullDelete($left_id, $right_id)
    {
        $this->delete($left_id, $right_id);
        $this->left->delete($left_id);
        $this->right->delete($right_id);
    }
}
