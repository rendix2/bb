<?php

namespace App\Models;

use App\Models\Crud\CrudManager;
use dibi;
use Dibi\Fluent;
use Dibi\Row;
use Nette\Caching\Cache;
use Nette\Utils\ArrayHash;

/**
 * Description of PostManager
 *
 * @author rendix2
 * @package App\Models
 */
class PostManager extends CrudManager
{

    public function getCountOfUsersByTopicId($topic_id)
    {
        return $this->dibi
                ->select('COUNT(post_id) AS post_count, post_user_id')
                ->from($this->getTable())
                ->where('[post_topic_id] = %i', $topic_id)
                ->groupBy('post_user_id')
                ->fetchAll();
    }


    public function getCountByUser($topic_id, $user_id)
    {
        return $this->getCountFluent()
            ->where('[post_topic_id] = %i', $topic_id)
            ->where('[post_user_id] = %i', $user_id)
            ->fetchSingle();
    }


    public function getFluentByTopic($topic_id)
    {
        return $this->getAllFluent()
            ->where('[post_topic_id] = %i', $topic_id);
    }

    public function getLastByUser($user_id)
    {
        return $this->getAllFluent()
            ->where('[post_id] = ',
                $this->dibi
                    ->select('MAX(post_id)')
                    ->from($this->getTable())
                    ->where('[post_user_id] = %i', $user_id)
            )->fetch();
    }


    public function copy($post_id, $target_topic_id = null)
    {
        $post = $this->getById($post_id);
        
        unset($post->post_id);
        
        if ($target_topic_id) {
            $post->post_topic_id = $target_topic_id;
        }
                
        return $this->add(ArrayHash::from($post->toArray()));
    }

    public function checkDoublePost($post_text, $user_id, $time)
    {
        return $this->dibi
            ->select('1')
            ->from($this->getTable())
            ->where('[post_text] = %s', $post_text)
            ->where('[post_user_id] = %i', $user_id)
            ->where('[post_add_time] >= %i', $time)
            ->fetchSingle();
    }
}
