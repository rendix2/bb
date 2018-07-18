<?php

namespace App\Models;

/**
 * Description of PostsHistoryManager
 *
 * @author rendi
 */
class PostsHistoryManager extends Crud\CrudManager
{
    public function deleteByPost($post_id)
    {
        return $this->dibi
                ->delete($this->getTable())
                ->where('%n = %i', 'post_id', $post_id)
                ->execute();
    }
    
    public function deleteByUser($user_id)
    {
        return $this->dibi
                ->delete($this->getTable())
                ->where('%n = %i', 'user_id', $user_id)
                ->execute();
    }    
    
}
