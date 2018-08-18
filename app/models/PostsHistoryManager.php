<?php

namespace App\Models;

use Dibi\Result;
use Dibi\Row;

/**
 * Description of PostsHistoryManager
 *
 * @author rendix2
 */
class PostsHistoryManager extends Crud\CrudManager
{
    /**
     *
     * @param int $post_id
     *
     * @return Result|int
     */
    public function deleteByPost($post_id)
    {
        return $this->dibi->delete($this->getTable())
            ->where('%n = %i', 'post_id', $post_id)
            ->execute();
    }

    /**
     *
     * @param int $user_id
     *
     * @return Result|int
     */
    public function deleteByUser($user_id)
    {
        return $this->dibi->delete($this->getTable())
            ->where('%n = %i', 'user_id', $user_id)
            ->execute();
    }

    /**
     *
     * @param int $post_id
     *
     * @return Row[]
     */
    public function getJoinedByPost($post_id)
    {
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
