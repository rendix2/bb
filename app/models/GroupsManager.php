<?php

namespace App\Models;

/**
 * Description of GroupsManager
 *
 * @author rendi
 */
class GroupsManager extends Crud\CrudManager {

    //put your code here

    public function addRealtion($data) {
        return $this->dibi->query('INSERT INTO user2group %m', $data);
    }
    
    public function getGrupsByUserId($user_id){
        return $this->dibi->select('group_id')->from('user2group')->where('[user_id] = %i', $user_id)->fetchPairs( null,'group_id');
    }
    
    public function deleteRelationByUserId($user_id){
        $this->dibi->delete('user2group')->where('[user_id] = %i', $user_id)->execute();
    }

}
