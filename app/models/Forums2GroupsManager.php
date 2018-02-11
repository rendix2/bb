<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\Models;

/**
 * Description of Forums2Groups
 *
 * @author rendi
 */
class Forums2GroupsManager extends MNManager {
    
    public function __construct(\Dibi\Connection $dibi, ForumsManager $left, GroupsManager $right) {
        parent::__construct($dibi, $left, $right);
    }

        public function addForums2group($group_id, $data){
            $this->deleteByRight($group_id);
        $this->dibi->query('INSERT INTO ['.$this->getTable().'] %m', $data);
        
    }   
}
