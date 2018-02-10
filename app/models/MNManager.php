<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\Models;

/**
 * Description of MNManager
 *
 * @author rendi
 */
abstract class MNManager extends Manager {

    private $left;
    private $right;
    private $table;

    public function __construct(\Dibi\Connection $dibi, \App\Models\Crud\CrudManager $left, \App\Models\Crud\CrudManager $right) {
        parent::__construct($dibi);

        $this->left  = $left;
        $this->right = $right;
        $this->table = $left->getTable() . '2' . $right->getTable();
    }

    private static function createAlias($tableName) {
        return substr($tableName, 0, 1);
    }

    public function add(array $values) {
        return $this->dibi->query('INSERT INTO [' . $this->table . '] %m', $values);
    }

    public function addByLeft($left_id, array $values) {
        $this->deleteByLeft($left_id);
        return $this->add($values);
    }

    public function addByRight($right_id, array $values) {
        $this->deleteByRight($right_id);
        return $this->add($values);
    }

    public function getByLeft($left_id) {
        return $this->dibi->select($this->right->getPrimaryKey())->from($this->table)->as($alias)->where('[' . $this->left->getPrimaryKey() . '] = %i', $left_id)->fetchAll();
    }

    public function getByRight($right_id) {
        return $this->dibi->select($this->left->getPrimaryKey())->from($this->table)->where('[' . $this->right->getPrimaryKey() . '] = %i', $right_id)->fetchAll();
    }

    public function getByLeftJoined($left_id) {
        $aliasL = self::createAlias($this->left->getTable());
        $aliasR = self::createAlias($this->right->getTable());

        return $this->dibi->select($aliasR . '.*')->from($this->table)->as('relation')->innerJoin($this->right->getTable())->as($aliasR)->on($aliasR . '.' . $this->right->getPrimaryKey() . ' = [relation.' . $this->right->getPrimaryKey() . ']')->where('[relation.' . $this->left->getPrimaryKey() . '] = %i', $left_id)->fetchAll();
    }

    public function getByRightJoined($right_id) {
        $aliasL = self::createAlias($this->left->getTable());
        $aliasR = self::createAlias($this->right->getTable());

        return $this->dibi->select($aliasL . '.*')->from($this->table)->as('relation')->innerJoin($this->left->getTable())->as($aliasL)->on($aliasL . '.' . $this->left->getPrimaryKey() . ' = [relation.' . $this->left->getPrimaryKey() . ']')->where('[relation.' . $this->right->getPrimaryKey() . '] = %i', $right_id)->fetchAll();
    }

    public function deleteByLeft($left_id) {
        return $this->dibi->delete($this->table)->where($this->left->getPrimaryKey() . ' = %i', $left_id)->execute();
    }

    public function deleteByRight($right_id) {
        return $this->dibi->delete($this->table)->where($this->right->getPrimaryKey() . ' = %i', $right_id)->execute();
    }

}
