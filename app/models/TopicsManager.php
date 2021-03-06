<?php

namespace App\Models;

use App\Models\Crud\CrudManager;
use dibi;
use Dibi\Fluent;
use Dibi\Row;
use Nette\Caching\Cache;
use Nette\Utils\ArrayHash;

/**
 * Description of TopicsManager
 *
 * @author rendix2
 * @package App\Models
 */
class TopicsManager extends CrudManager
{

    /**
     * @return Row|false
     */
    public function getLast()
    {
        return $this->getAllFluent()
            ->where('[topic_id] = ', $this->dibi
                ->select('MAX(topic_id)')
                ->from($this->getTable()))
            ->fetch();
    }

    /**
     * @param int $forum_id
     *
     * @return Row|false
     */
    public function getLastByForum($forum_id)
    {
        return $this->getAllFluent()
            ->where('[topic_id] = ', $this->dibi
                ->select('MAX(topic_id)')
                ->from($this->getTable())
                ->where('[topic_forum_id] = %i', $forum_id))
            ->fetch();
    }

    /**
     * @param int $forum_id
     * @param int $topic_time
     *
     * @return array|mixed
     */
    public function getNewerTopicsCached($forum_id, $topic_time)
    {
        $key    = $forum_id . '-' . $topic_time;
        $cached = $this->managerCache->load($key);

        if (!isset($cached)) {
            $this->managerCache->save(
                $key,
                $cached = $this->getAllFluent()
                    ->where('[topic_forum_id] = %i', $forum_id)
                    ->where('[topic_add_time] > %i', $topic_time)
                    ->fetchAll(),
                [Cache::EXPIRE => '2 hours']
            );
        }

        return $cached;
    }

    /**
     * @return Row|false
     */
    public function getUserWithMostTopic()
    {
        return $this->dibi
            ->select('COUNT(t.topic_id) AS topic_count, u.user_id, u.user_name')
            ->from($this->getTable())
            ->as('t')
            ->innerJoin(self::USERS_TABLE)
            ->as('u')
            ->on('[t.topic_user_id] = [u.user_id]')
            ->groupBy('topic_user_id', dibi::ASC)
            ->fetch();
    }

    /**
     * @param string $topic_name
     *
     * @return array
     */
    public function findByTopicNameJoinedUser($topic_name)
    {
        return $this->getAllFluent()
            ->innerJoin(self::USERS_TABLE)
            ->on('[topic_user_id] = user_id')
            ->where('MATCH([topic_name]) AGAINST (%s IN BOOLEAN MODE)', $topic_name)
            ->fetchAll();
    }

    /**
     * @param int $forum_id
     *
     * @return Fluent
     */
    public function getFluentByForumJoinedUsersJoinedLastPost($forum_id)
    {
        return $this->getAllFluent()
            ->as('t')
            ->leftJoin(self::USERS_TABLE)
            ->as('u')
            ->on('[t.topic_user_id] = [u.user_id]')
            ->leftJoin(self::POSTS_TABLE)
            ->as('p')
            ->on('[p.post_id] = [t.topic_last_post_id]')
            ->where('[t.topic_forum_id] = %i', $forum_id);
    }
    
    /**
     *
     * @param int $forum_id forum_id
     *
     * @return Row[]
     */
    public function getAllByForum($forum_id)
    {
        return $this->getAllFluent()
                ->where('[topic_forum_id] = %i', $forum_id)
                ->fetchAll();
    }

    /**
     * @param int $user_id
     *
     * @return Fluent
     */
    public function getFluentByUser($user_id)
    {
        return $this->getAllFluent()
                ->where('[topic_user_id] = %i', $user_id);
    }
    
    /**
     *
     * @param int $topic_id
     * @param int|null $target_forum_id
     *
     * @return int
     */
    public function copy($topic_id, $target_forum_id = null)
    {
        $topic = $this->getById($topic_id);
        
        unset($topic->topic_id);
        
        if ($target_forum_id) {
            $topic->topic_forum_id = $target_forum_id;
        }

        return $this->add(ArrayHash::from($topic->toArray()));
    }
}
