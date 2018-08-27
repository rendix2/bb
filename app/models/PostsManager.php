<?php

namespace App\Models;

use dibi;
use Dibi\Fluent;
use Dibi\Row;
use Nette\Caching\Cache;
use Nette\Utils\ArrayHash;

/**
 * Description of PostManager
 *
 * @author rendix2
 */
class PostsManager extends Crud\CrudManager
{   
    /**
     * @param int $category_id
     *
     * @return mixed
     */
    public function getCountByCategory($category_id)
    {
        return $this->dibi
            ->select('COUNT(post_id)')
            ->from($this->getTable())
            ->where('[post_category_id] = %i', $category_id)
            ->fetchSingle();
    }

    /**
     * @param int $forum_id
     *
     * @return mixed
     */
    public function getCountByForum($forum_id)
    {
        return $this->dibi
                ->select('COUNT(post_id)')
                ->from($this->getTable())
                ->where('[post_forum_id] = %i', $forum_id)
                ->fetchSingle();
    }

    /**
     * @param int $topic_id
     *
     * @return mixed
     */
    public function getCountByTopic($topic_id)
    {
        return $this->dibi
                ->select('COUNT(post_id)')
                ->from($this->getTable())
                ->where('[post_topic_id] = %i', $topic_id)
                ->fetchSingle();
    }

    /**
     * @param int $topic_id
     *
     * @return array
     */
    public function getCountOfUsersByTopicId($topic_id)
    {
        return $this->dibi
                ->select('COUNT(post_id) AS post_count, post_user_id')
                ->from($this->getTable())
                ->where('[post_topic_id] = %i', $topic_id)
                ->groupBy('post_user_id')
                ->fetchAll();
    }

    /**
     * @param int $topic_id
     * @param int $user_id
     *
     * @return mixed
     */
    public function getCountByUser($topic_id, $user_id)
    {
        return $this->getCountFluent()
            ->where('[post_topic_id] = %i', $topic_id)
            ->where('[post_user_id] = %i', $user_id)
            ->fetchSingle();
    }

    /**
     * @param int $forum_id
     *
     * @return Row|false
     */
    public function getLastByForum($forum_id)
    {
        return $this->getAllFluent()
                ->where('[post_id] = ',
                    $this->dibi
                    ->select('MAX(post_id)')
                    ->from($this->getTable())
                    ->where('[post_forum_id] = %i', $forum_id)
                )->fetch();        
    }

    /**
     * @param int $topic_id
     *
     * @return Row|false
     */
    public function getLastByTopic($topic_id)
    {
        return $this->getAllFluent()
                ->where('[post_id] = ',
                    $this->dibi
                        ->select('MAX(post_id)')
                        ->from($this->getTable())
                        ->where('[post_topic_id] = %i', $topic_id)
                )->fetch();
    }
    
    /**
     * @param int $topic_id
     *
     * @return Row|false
     */
    public function getFirstByTopic($topic_id)
    {
        return $this->getAllFluent()
                ->where('[post_id] = ',
                    $this->dibi
                        ->select('MIN(post_id)')
                        ->from($this->getTable())
                        ->where('[post_topic_id] = %i', $topic_id)
                )->fetch();
    }

    /**
     * @param int $forum_id
     * @param int $post_time
     *
     * @return array|mixed
     */
    public function getNewerPosts($forum_id, $post_time)
    {
        $key    = $forum_id . '-' . $post_time;
        $cached = $this->managerCache->load($key);

        if (!isset($cached)) {
            $this->managerCache->save(
                $key,
                $cached = $this->getAllFluent()
                    ->where('[post_forum_id] = %i', $forum_id)
                    ->where('[post_add_time] > %i', $post_time)
                    ->fetchAll(),
                [
                    Cache::EXPIRE => '2 hours',
                ]
            );
        }

        return $cached;
    }
    
    /**
     * @return Row|false
     */
    public function getUserWithMostPosts()
    {
        return $this->dibi
                ->select('COUNT(p.post_id) AS post_count, u.user_id, u.user_name')
                ->from($this->getTable())
                ->as('p')
                ->innerJoin(self::USERS_TABLE)
                ->as('u')
                ->on('[p.post_user_id] = [u.user_id]')
                ->groupBy('post_user_id', dibi::ASC)
                ->fetch();
    }

    /**
     * @param int $topic_id
     *
     * @return Fluent
     */
    public function getByTopicJoinedUser($topic_id)
    {
        return $this->getAllFluent()
            ->as('p')
            ->innerJoin(self::USERS_TABLE)
            ->as('u')
            ->on('[p.post_user_id] = [u.user_id]')
            ->where('[post_topic_id] = %i', $topic_id);
    }
    
    /**
     * @param int $topic_id
     *
     * @return Fluent
     */
    public function getByTopic($topic_id)
    {
        return $this->getAllFluent()
            ->where('[post_topic_id] = %i', $topic_id);
    }    

    /**
     * @param $user_id
     *
     * @return Fluent
     */
    public function getByUser($user_id)
    {
        return $this->getAllFluent()
                ->where('[post_user_id] = %i', $user_id);
    }
     

    /**
     * @param string $post_text
     *
     * @return array
     */
    public function findPosts($post_text)
    {
        return $this->getAllFluent()
            ->as('p')
            ->leftJoin(self::TOPICS_TABLE)
            ->as('t')
            ->on('[p.post_topic_id] = [t.topic_id]')
            ->where('MATCH([p.post_title],[p.post_text]) AGAINST(%s IN BOOLEAN MODE)', $post_text)
            ->fetchAll();
    }
    
    /**
     * 
     * @param int $user_id
     * 
     * @return Row
     */
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

    /**
     *
     * @param int $post_id
     * @param int $target_topic_id
     *
     * @return int
     */
    public function copy($post_id, $target_topic_id = null)
    {
        $post = $this->getById($post_id);
        
        unset($post->post_id);
        
        if ($target_topic_id) {
            $post->post_topic_id = $target_topic_id;
        }
                
        return $this->add(ArrayHash::from($post->toArray()));
    }
    
    /**
     *
     * @param string $post_text
     * @param int    $user_id
     * @param int    $time
     *
     * @return int
     */
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
