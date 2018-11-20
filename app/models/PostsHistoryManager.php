<?php

namespace App\Models;

use App\Models\Crud\CrudManager;
use Dibi\Result;
use Dibi\Row;

/**
 * Description of PostsHistoryManager
 *
 * @author rendix2
 * @package App\Models
 */
class PostsHistoryManager extends CrudManager
{
    /**
     *
     * @param int $post_id
     *
     * @return Result|int
     */
    public function deleteByPost($post_id)
    {
        return $this->deleteFluent()
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
        return $this->deleteFluent()
            ->where('%n = %i', 'user_id', $user_id)
            ->execute();
    }

    /**
     * @param int $post_id
     *
     * @return Row[]
     */
    public function getByPost($post_id)
    {
        return $this->getAllFluent()
            ->where('[post_id] = %i', $post_id)
            ->fetchAll();
    }
}
