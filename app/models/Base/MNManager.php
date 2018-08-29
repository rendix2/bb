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
        
        if ($tableName === null) {
            $this->table = $left->getTable() . '2' . $right->getTable();
        } else {
            $this->table = $tableName;
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
     * @param $left_id
     *
     * @return Fluent
     */
    public function getAllFluentByLeft($left_id)
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
        return $this->getAllFluentByLeft($left_id)->fetchAll();
    }
    
    /**
     * @param int $left_id
     *
     * @return Fluent
     */
    public function getFluentJoinedByLeft($left_id)
    {
        $aliasR = self::createAlias($this->right->getTable());

        return $this->dibi->select($aliasR . '.*')
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
    public function getAllJoinedByLeft($left_id)
    {
        return $this->getFluentJoinedByLeft($left_id)->fetchAll();
    }

    /**
     * @param int $left_id
     *
     * @return Row[]
     */
    public function getPairsByLeft($left_id)
    {
        return $this->getAllFluentByLeft($left_id)
            ->fetchPairs(null, $this->right->getPrimaryKey());
    }
    
        /**
     * @param int $right_id
     *
     * @return Fluent
     */
    public function getAllFluentByRight($right_id)
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
        return $this->getAllFluentByRight($right_id)->fetchAll();
    }
    
    /**
     * @param int $right_id
     *
     * @return Fluent
     */
    public function getFluentJoinedByRight($right_id)
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
    public function getAllJoinedByRight($right_id)
    {
        return $this->getFluentJoinedByRight($right_id)
            ->fetchAll();
    }
    
    /**
     * @param int $right_id
     *
     * @return Row[]
     */
    public function getPairsByRight($right_id)
    {
        return $this->getFluentJoinedByRight($right_id)
            ->fetchPairs(null, $this->left->getPrimaryKey());
    }

    /**
     * @param int $left_id
     * @param int $right_id
     *
     * @return Row[]
     */
    public function getFullJoined($left_id, $right_id)
    {
        $aliasL = self::createAlias($this->left->getTable());
        $aliasR = self::createAlias($this->right->getTable());

        if ($aliasL === $aliasR) {
            $aliasL = $this->left->getTable();
            $aliasR = $this->right->getTable();
        }

        return $this->dibi->select($aliasL . '.*')
            ->from($this->table)
            ->as('relation')
            ->innerJoin($this->left->getTable())
            ->as($aliasL)
            ->on($aliasL . '.' . $this->left->getPrimaryKey() . ' = [relation.' . $this->leftKey . ']')
            ->innerJoin($this->right->getTable())
            ->as($aliasL)
            ->on($aliasR . '.' . $this->right->getPrimaryKey() . ' = [relation.' . $this->rightKey . ']')
            ->where('[relation.' . $this->leftKey . '] = %i', $left_id)
            ->where('[relation.' . $this->rightKey . '] = %i', $right_id)
            ->fetchAll();
    }

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
        return $this->getCountFluent()->fetchSingle();
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
     * @param array    $values
     * @param int|null $left_id
     * @param int|null $right_id
     *
     * @return Result|int|void
     */
    public function add(array $values, $left_id = null, $right_id = null)
    {
        if (!count($values)) {
            return NAN;
        }
        
        $data = [];

        foreach ($values as $value) {
            $data[$this->leftKey][]  = $left_id !== null ? (int)$left_id : (int)$value;
            $data[$this->rightKey][] = $right_id !== null ? (int)$right_id : (int)$value;
        }
                
        return $this->dibi->query('INSERT INTO %n %m', $this->table, $data);
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
     * @param int $left_id
     *
     * @return Result|int
     */
    public function deleteByLeft($left_id)
    {
        return $this->dibi
            ->delete($this->table)
            ->where('%n = %i', $this->leftKey, $left_id)
            ->execute();
    }

    /**
     * @param int $right_id
     *
     * @return Result|int
     */
    public function deleteByRight($right_id)
    {
        return $this->dibi
            ->delete($this->table)
            ->where('%n = %i', $this->rightKey, $right_id)
            ->execute();
    }

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
     * deletes relation
     *
     * @param int $left_id
     * @param int $right_id
     *
     * @return Result|int
     */
    public function fullDelete($left_id, $right_id)
    {
        return $this->dibi
            ->delete($this->table)
            ->where('%n = %i', $this->leftKey, $left_id)
            ->where('%n = %i', $this->rightKey, $right_id)
            ->execute();
    }

    /**
     * returns all table

     * @return Row[]
     */
    public function getAll()
    {
        return $this->getAllFluent()
                ->fetchAll();
    }

    /**
     * @return Fluent
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
}
