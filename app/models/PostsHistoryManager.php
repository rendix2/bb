<?php

namespace App\Models;

/**
 * Description of PostsHistoryManager
 *
 * @author rendi
 */
class PostsHistoryManager extends Crud\CrudManager
{
    /**
     * 
     * @param int $post_id
     * 
     * @return type
     */
    public function deleteByPost($post_id)
    {
        return $this->dibi
                ->delete($this->getTable())
                ->where('%n = %i', 'post_id', $post_id)
                ->execute();
    }
    
    /**
     * 
     * @param int $user_id
     * 
     * @return type
     */
    public function deleteByUser($user_id)
    {
        return $this->dibi
                ->delete($this->getTable())
                ->where('%n = %i', 'user_id', $user_id)
                ->execute();
    }   
    
    /**
     * 
     * @param int $post_id
     * 
     * @return Row[]
     */
    public function getJoinedByPost($post_id) {
        return $this->dibi
                ->select('p.*')
                ->from($this->getTable())
                ->as('ph')
                ->innerJoin(self::POSTS_TABLE)
                ->as('p')
                ->on('%n = %n', 'ph.post_id', 'p.post_id')
                ->where('%n = %i', 'p.post_id', $post_id)
                ->fetchAll();
    }    
}