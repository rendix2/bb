<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\Models;

use App\Models\Crud\CrudManager;
use Dibi\Connection;
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
     */
    public function __construct(Connection $dibi, CrudManager $left, CrudManager $right)
    {
        parent::__construct($dibi);

        $this->left  = $left;
        $this->right = $right;
        $this->table = $left->getTable() . '2' . $right->getTable();
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
     * @return array
     */
    public function getByLeftJoined($left_id)
    {
        $aliasL = self::createAlias($this->left->getTable());
        $aliasR = self::createAlias($this->right->getTable());

        return $this->dibi->select($aliasR . '.*')
                          ->from($this->table)
                          ->as('relation')
                          ->innerJoin($this->right->getTable())
                          ->as($aliasR)
                          ->on($aliasR . '.' . $this->right->getPrimaryKey() . ' = [relation.' . $this->right->getPrimaryKey() . ']')
                          ->where('[relation.' . $this->left->getPrimaryKey() . '] = %i', $left_id)
                          ->fetchAll();
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
     * @return array
     */
    public function getByRightJoined($right_id)
    {
        $aliasL = self::createAlias($this->left->getTable());
        $aliasR = self::createAlias($this->right->getTable());

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
     * @param int $right_id
     *
     * @return array
     */
    public function getByRightPairs($right_id)
    {
        return $this->dibi->select($this->left->getPrimaryKey())
                          ->from($this->table)
                          ->where('[' . $this->right->getPrimaryKey() . '] = %i', $right_id)
                          ->fetchPairs(null, $this->left->getPrimaryKey());
    }

    /**
     * @param int $left_id
     *
     * @return mixed
     */
    public function getCountByLeft($left_id)
    {
        return $this->dibi->select('COUNT(*)')
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
        return $this->dibi->select('COUNT(*)')
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
     * @return Result|int
     */
    public function add(array $values, $left_id = null, $right_id = null)
    {
        $data = [];

        foreach ($values as $value) {
            $data[$this->left->getPrimaryKey()][]  = $left_id !== null ? (int)$left_id : (int)$value;
            $data[$this->right->getPrimaryKey()][] = $right_id !== null ? (int)$right_id : (int)$value;
        }

        return $this->dibi->query('INSERT INTO [' . $this->table . '] %m', $data);
    }

    /**
     * @param       $left_id
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
     * @param $tableName
     *
     * @return bool|string
     */
    private static function createAlias($tableName)
    {
        return substr($tableName, 0, 1);
    }

    /**
     * @param int $left_id
     *
     * @return Result|int
     */
    public function deleteByLeft($left_id)
    {
        return $this->dibi->delete($this->table)->where($this->left->getPrimaryKey() . ' = %i', $left_id)->execute();
    }

    /**
     * @param int $right_id
     *
     * @return Result|int
     */
    public function deleteByRight($right_id)
    {
        return $this->dibi->delete($this->table)->where($this->right->getPrimaryKey() . ' = %i', $right_id)->execute();
    }

}
