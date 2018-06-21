<?php

namespace App\Models;

use App\Models\Crud\CrudManager;
use Dibi\Connection;
use Dibi\Fluent;
use Dibi\Result;

/**
 * Description of MNManager
 *
 * @author rendi
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
     * MNManager constructor.
     *
     * @param Connection  $dibi
     * @param CrudManager $left
     * @param CrudManager $right
     * @param string|null $tableName
     */
    public function __construct(Connection $dibi, CrudManager $left, CrudManager $right, $tableName = null)
    {
        parent::__construct($dibi);

        $this->left  = $left;
        $this->right = $right;

        if ($tableName === null) {
            $this->table = $left->getTable() . '2' . $right->getTable();
        } else {
            $this->table = $tableName;
        }
    }

    /**
     * @param int $left_id
     *
     * @return array
     */
    public function getByLeftAll($left_id)
    {
        return $this->dibi->select('*')
            ->from($this->table)
            ->where('[' . $this->left->getPrimaryKey() . '] = %i', $left_id)
            ->fetchAll();
    }
    
    /**
     * @param int $left_id
     *
     * @return Fluent
     */
    public function getByLeftJoinedFluent($left_id)
    {
        $aliasR = self::createAlias($this->right->getTable());

        return $this->dibi->select($aliasR . '.*')
            ->from($this->table)
            ->as('relation')
            ->innerJoin($this->right->getTable())
            ->as($aliasR)
            ->on($aliasR . '.' . $this->right->getPrimaryKey() . ' = [relation.' . $this->right->getPrimaryKey() . ']')
            ->where('[relation.' . $this->left->getPrimaryKey() . '] = %i', $left_id);
    }

    /**
     * @param int $left_id
     *
     * @return array
     */
    public function getByLeftJoined($left_id)
    {
        $aliasR = self::createAlias($this->right->getTable());

        return $this->getByLeftJoinedFluent()->fetchAll();
    }

    /**
     * @param int $left_id
     *
     * @return array
     */
    public function getByLeftPairs($left_id)
    {
        return $this->dibi->select($this->right->getPrimaryKey())
            ->from($this->table)
            ->where('[' . $this->left->getPrimaryKey() . '] = %i', $left_id)
            ->fetchPairs(null, $this->right->getPrimaryKey());
    }

    /**
     * @param int $right_id
     *
     * @return array
     */
    public function getByRightAll($right_id)
    {
        return $this->dibi->select('*')
            ->from($this->table)
            ->where('[' . $this->right->getPrimaryKey() . '] = %i', $right_id)
            ->fetchAll();
    }
    
    /**
     * @param int $right_id
     *
     * @return Fluent
     */
    public function getByRightJoinedFluent($right_id)
    {
        $aliasL = self::createAlias($this->left->getTable());

        return $this->dibi->select($aliasL . '.*')
            ->from($this->table)
            ->as('relation')
            ->innerJoin($this->left->getTable())
            ->as($aliasL)
            ->on($aliasL . '.' . $this->left->getPrimaryKey() . ' = [relation.' . $this->left->getPrimaryKey() . ']')
            ->where('[relation.' . $this->right->getPrimaryKey() . '] = %i', $right_id);
    }

    /**
     * @param int $right_id
     *
     * @return array
     */
    public function getByRightJoined($right_id)
    {
        $aliasL = self::createAlias($this->left->getTable());

        return $this->dibi->select($aliasL . '.*')
            ->from($this->table)
            ->as('relation')
            ->innerJoin($this->left->getTable())
            ->as($aliasL)
            ->on($aliasL . '.' . $this->left->getPrimaryKey() . ' = [relation.' . $this->left->getPrimaryKey() . ']')
            ->where('[relation.' . $this->right->getPrimaryKey() . '] = %i', $right_id)
            ->fetchAll();
    }

    /**
     * @param int $left_id
     * @param int $right_id
     *
     * @return array
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
            ->on($aliasL . '.' . $this->left->getPrimaryKey() . ' = [relation.' . $this->left->getPrimaryKey() . ']')
            ->innerJoin($this->right->getTable())
            ->as($aliasL)
            ->on($aliasR . '.' . $this->right->getPrimaryKey() . ' = [relation.' . $this->right->getPrimaryKey() . ']')
            ->where('[relation.' . $this->left->getPrimaryKey() . '] = %i', $left_id)
            ->where('[relation.' . $this->right->getPrimaryKey() . '] = %i', $right_id)
            ->fetchAll();
    }

    /**
     * @param int $right_id
     *
     * @return array
     */
    public function getByRightPairs($right_id)
    {
        return $this->dibi
            ->select($this->left->getPrimaryKey())
            ->from($this->table)
            ->where('[' . $this->right->getPrimaryKey() . '] = %i', $right_id)
            ->fetchPairs(null, $this->left->getPrimaryKey());
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
     * @param int $left_id
     *
     * @return mixed
     */
    public function getCountByLeft($left_id)
    {
        return $this->dibi
            ->select('COUNT(*)')
            ->from($this->table)
            ->where($this->left->getPrimaryKey() . ' = %i', $left_id)
            ->fetchSingle();
    }

    /**
     * @param int $right_id
     *
     * @return mixed
     */
    public function getCountByRight($right_id)
    {
        return $this->dibi
            ->select('COUNT(*)')
            ->from($this->table)
            ->where($this->right->getPrimaryKey() . ' = %i', $right_id)
            ->fetchSingle();
    }

    /**
     * @return string
     */
    public function getTable()
    {
        return $this->table;
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
            $data[$this->left->getPrimaryKey()][]  = $left_id !== null ? (int)$left_id : (int)$value;
            $data[$this->right->getPrimaryKey()][] = $right_id !== null ? (int)$right_id : (int)$value;
        }
                
            return $this->dibi->query('INSERT INTO [' . $this->table . '] %m', $data);
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
     * @param string $tableName
     *
     * @return bool|string
     */
    private static function createAlias($tableName)
    {
        return mb_substr($tableName, 0, 1);
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
            ->where('[' . $this->left->getPrimaryKey() . '] = %i', $left_id)
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
            ->where('[' . $this->right->getPrimaryKey() . '] = %i', $right_id)
            ->execute();
    }

    /**
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
                ->where('[' . $this->left->getPrimaryKey() . '] = %i', $left_id)
                ->where('[' . $this->right->getPrimaryKey() . '] = %i', $right_id)
                ->fetchSingle() === 1;
    }

    /**
     * @param int $left_id
     * @param int $right_id
     *
     * @return Result|int
     */
    public function fullDelete($left_id, $right_id)
    {
        return $this->dibi
            ->delete($this->table)
            ->where('[' . $this->left->getPrimaryKey() . '] = %i', $left_id)
            ->where('[' . $this->right->getPrimaryKey() . '] = %i', $right_id)
            ->execute();
    }

    /**
     * @return array
     */
    /**
     * @return array
     */
    public function getAll()
    {
        return $this->getAllFluent()->fetchAll();
    }

    /**
     * @return Fluent
     */
    /**
     * @return Fluent
     */
    public function getAllFluent()
    {
        return $this->dibi->select('*')->from($this->table);
    }
}
