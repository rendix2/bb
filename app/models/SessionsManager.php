<?php

namespace App\Models;

use Nette\Utils\ArrayHash;

/**
 * Description of SessionsManager
 *
 * @author rendi
 */
class SessionsManager extends Crud\CrudManager
{

    /**
     * @return mixed
     */
    public function getCountOfLoggedUsers()
    {
        return $this->dibi
            ->query('SELECT COUNT(DISTINCT session_user_id) FROM %n', $this->getTable())
            ->fetchSingle();
    }

    /**
     * @return array
     */
    public function getLoggedUsers()
    {
        return $this->dibi
                ->select('')
                ->distinct('session_user_id, user_id, user_name, session_from, session_last_activity')
                ->from($this->getTable())
                ->as('s')
                ->innerJoin(self::USERS_TABLE)
                ->as('u')
                ->on('[s.session_user_id] = [u.user_id]')
                ->fetchAll();
    }

    /**
     * @param int $session_id
     *
     * @return \Dibi\Result|int
     */
    public function deleteBySession($session_id)
    {
        return $this->dibi
                ->delete($this->getTable())
                ->where('[session_key] = %s', $session_id)
                ->execute();
    }

    /**
     * @param int $user_id
     *
     * @return \Dibi\Result|int
     */
    public function deleteByUser($user_id)
    {
        return $this->dibi
                ->delete($this->getTable())
                ->where('[session_user_id] = %i', $user_id)
                ->execute();
    }

    /**
     * @param                        $session_key
     * @param ArrayHash              $session_data
     *
     * @return \Dibi\Result|int
     */
    public function updateBySessionsKey($session_key, ArrayHash $session_data)
    {
        return $this->dibi
                ->update($this->getTable(), $session_data)
                ->where('[session_key] = %s', $session_key)
                ->execute();
    }

    /**
     * @param                        $user_id
     * @param ArrayHash              $session_data
     */
    public function updateByUser($user_id, ArrayHash $session_data)
    {
        $this->dibi
                ->update($this->getTable(), $session_data)
                ->where('[session_user_id] = %i', $user_id)
                ->execute();
    }
    
    /**
     * 
     * @return bool
     */
    public function truncateSessions()
    {
        return $this->dibi->query('TRUNCATE TABLE %n', $this->getTable());
    }
}
