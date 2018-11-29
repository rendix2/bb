<?php

namespace App\Models;

use App\Models\Crud\CrudManager;
use Dibi\Connection;
use Dibi\Fluent;
use Dibi\Result;
use Dibi\Row;

/**
 * Description of MNManager
 *
 * @author rendix2
 * @package App\Models
 */
abstract class MNManager extends Manager
{
    /**
     * @var CrudManager $left
     */
    private $left;
    
    /**
     * @var CrudManager $right
     */
    private $right;
    
    /**
     * @var string $table
     */
    private $table;
    
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
     * MNManager constructor.
     *
     * @param Connection  $dibi
     * @param CrudManager $left
     * @param CrudManager $right
     * @param string|null $tableName
     * @param null        $leftKey
     * @param null        $rightKey
     */
    public function __construct(
        Connection $dibi,
        CrudManager $left,
        CrudManager $right,
        $tableName = null,
        $leftKey = null,
        $rightKey = null
    ) {
        parent::__construct($dibi);

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
    }
    
    public function __destruct()
    {
        $this->left     = null;
        $this->right    = null;
        $this->table    = null;
        $this->leftKey  = null;
        $this->rightKey = null;
                
        parent::__destruct();
    }

    /**
     * @param string $tableName
     *
     * @return bool|string
     */
    private static function createAlias($tableName)
    {
        return mb_substr($tableName, 0, 1);
    }

    /**
     * @return string
     */
    public function getTable()
    {
        return $this->table;
    }
    
    /**
     * get all
     */
    
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
        $aliasR = self::createAlias($this->right->getTable());

        return $this->dibi
            ->select($aliasR . '.*')
            ->from($this->table)
            ->as('relation')
            ->innerJoin($this->right->getTable())
            ->as($aliasR)
            ->on($aliasR . '.' . $this->right->getPrimaryKey() . ' = [relation.' . $this->rightKey . ']')
            ->where('[relation.' . $this->leftKey . '] = %i', $left_id);
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
        $aliasR = self::createAlias($this->right->getTable());

        return $this->dibi
            ->select('*')
            ->from($this->table)
            ->as('relation')
            ->innerJoin($this->right->getTable())
            ->as($aliasR)
            ->on($aliasR . '.' . $this->right->getPrimaryKey() . ' = [relation.' . $this->rightKey . ']')
            ->where('[relation.' . $this->leftKey . '] IN %in', $left_id);
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
        $aliasL = self::createAlias($this->left->getTable());

        return $this->dibi->select($aliasL . '.*')
            ->from($this->table)
            ->as('relation')
            ->innerJoin($this->left->getTable())
            ->as($aliasL)
            ->on($aliasL . '.' . $this->left->getPrimaryKey() . ' = [relation.' . $this->leftKey . ']')
            ->where('[relation.' . $this->rightKey . '] = %i', $right_id);
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
        $aliasL = self::createAlias($this->left->getTable());

        return $this->dibi->select('*')
            ->from($this->table)
            ->as('relation')
            ->innerJoin($this->left->getTable())
            ->as($aliasL)
            ->on($aliasL . '.' . $this->left->getPrimaryKey() . ' = [relation.' . $this->leftKey . ']')
            ->where('[relation.' . $this->rightKey . '] IN %in', $right_id);
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
            ->as('relation')
            ->where('[relation.' . $this->leftKey . '] = %i', $left_id)
            ->where('[relation.' . $this->rightKey . '] = %i', $right_id);
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
        $aliasL = self::createAlias($this->left->getTable());
        $aliasR = self::createAlias($this->right->getTable());

        if ($aliasL === $aliasR) {
            $aliasL = $this->left->getTable();
            $aliasR = $this->right->getTable();
        }

        return $this->dibi
            ->select('*')
            ->from($this->table)
            ->as('relation')
            ->innerJoin($this->left->getTable())
            ->as($aliasL)
            ->on($aliasL . '.' . $this->left->getPrimaryKey() . ' = [relation.' . $this->leftKey . ']')
            ->innerJoin($this->right->getTable())
            ->as($aliasR)
            ->on($aliasR . '.' . $this->right->getPrimaryKey() . ' = [relation.' . $this->rightKey . ']')
            ->where('[relation.' . $this->leftKey . '] = %i', $left_id)
            ->where('[relation.' . $this->rightKey . '] = %i', $right_id)
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
     * @return Fluent
     */
    private function getCountFluent()
    {
        return $this->dibi
            ->select('COUNT(*)')
            ->from($this->table);
    }

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
     * @return Fluent
     */
    public function deleteFluent()
    {
        return $this->dibi
            ->delete($this->table);
    }
    
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
