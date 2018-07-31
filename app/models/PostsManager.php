<?php

namespace App\Models;

use dibi;
use Dibi\Fluent;
use Dibi\Row;
use Nette\Caching\Cache;

/**
 * Description of PostManager
 *
 * @author rendi
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
                ->select('COUNT(post_id) as post_count, post_user_id')
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
        return $this->dibi->select('COUNT(*)')
            ->from($this->getTable())
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
        return $this->dibi->query('SELECT *
                FROM %n
                WHERE [post_id] = ( SELECT MAX(post_id) FROM %n WHERE [post_forum_id] = %i )',
            $this->getTable(),
            $this->getTable(),
            $forum_id
        )
            ->fetch();
    }

    /**
     * @param int $topic_id
     *
     * @return Row|false
     */
    public function getLastByTopic($topic_id)
    {
        return $this->dibi->query(
            'SELECT *
            FROM %n
            WHERE [post_id] = ( SELECT MAX(post_id) FROM %n WHERE [post_topic_id] = %i )',
            $this->getTable(),
            $this->getTable(),
            $topic_id
        )->fetch();
    }
    
    /**
     * @param int $topic_id
     *
     * @return Row|false
     */
    public function getFirstByTopic($topic_id)
    {
        return $this->dibi->query(
            'SELECT * FROM %n WHERE [post_id] = ( SELECT MIN(post_id) FROM %n WHERE [post_topic_id] = %i )',
            $this->getTable(),
            $this->getTable(),
            $topic_id
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
                $cached = $this->dibi
                    ->select('*')
                    ->from($this->getTable())
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
                ->select('COUNT(p.post_id) as post_count, u.user_id, u.user_name')
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
    public function getByTopic($topic_id)
    {
        return $this->dibi
            ->select('*')
            ->from($this->getTable())
            ->as('p')
            ->innerJoin(self::USERS_TABLE)
            ->as('u')
            ->on('[p.post_user_id] = [u.user_id]')
            ->where('[post_topic_id] = %i', $topic_id);
    }

    /**
     * @param $user_id
     *
     * @return Fluent
     */
    public function getByUser($user_id)
    {
        return $this->dibi
                ->select('*')
                ->from($this->getTable())
                ->where('[post_user_id] = %i', $user_id);
    }
     

    /**
     * @param string $post_text
     *
     * @return array
     */
    public function findPosts($post_text)
    {
        return $this->dibi
            ->select('*')
            ->from($this->getTable())
            ->as('p')
            ->leftJoin(self::TOPICS_TABLE)
            ->as('t')
            ->on('[p.post_topic_id] = [t.topic_id]')
            ->where('MATCH([p.post_title],[p.post_text]) AGAINST(%s IN BOOLEAN MODE)', $post_text)
            ->fetchAll();
    }
}
