<?php

namespace App\Models;

/**
 * Description of SessionsManager
 *
 * @author rendi
 */
class SessionsManager extends Crud\CrudManager {

    public function deleteByUserId($user_id){
        $this->dibi->delete($this->getTable())->where('[session_user_id] = %i', $user_id)->execute();    
    }
    
    public function deleteBySessionId($session_id){
        $this->dibi->delete($this->getTable())->where('[session_key] = %s', $session_id)->execute();
    }
    
    public function getLoggedUsers(){
        return $this->dibi->select('')->distinct('session_user_id, user_id, user_name, session_from, session_last_activity')->from($this->getTable())->as('s')->innerJoin(self::USERS_TABLE)->as('u')->on('[s.session_user_id] = [u.user_id]')->fetchAll();
    }
    
    public function getCountOfLoggedUsers(){
        return $this->dibi->query('SELECT count(DISTINCT session_user_id) FROM ['.$this->getTable().']')->fetchSingle();
    }
    
    public function updateBySessionsKey($session_key, \Nette\Utils\ArrayHash $session_data){
        $this->dibi->update($this->getTable(), $session_data)->where('[session_key] = %s', $session_key)->execute();
    }
    
    public function updateByUserId($user_id, \Nette\Utils\ArrayHash $session_data){
        $this->dibi->update($this->getTable(), $session_data)->where('[session_user_id] = %i', $user_id)->execute();
    }    
    
}
