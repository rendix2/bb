<?php

namespace App\Models;

/**
 * Description of RolesManager
 *
 * @author rendi
 */
class RolesManager extends Crud\CrudManager {
    
    public function getForSelect(){
        return $this->dibi->select('*')->from($this->getTable())->fetchPairs('role_id', 'role_name');
    }
    
}
