<?php

namespace App\Models;

use Dibi\Fluent;
use Dibi\Row;
use Nette\Caching\Cache;

/**
 * Description of TopicsManager
 *
 * @author rendix2
 */
class TopicsManager extends Crud\CrudManager
{

    /**
     * @return Row|false
     */
    public function getLastTopic()
    {
        return $this->getAllFluent()
                ->where('[topic_id] = ', 
                    $this->dibi
                        ->select('MAX(topic_id)')
                        ->from($this->getTable())
                        
                )
                ->fetch();
    }

    /**
     * @param int $forum_id
     *
     * @return Row|false
     */
    public function getLastTopicByForum($forum_id)
    {
        return $this->getAllFluent()
                ->where('[topic_id] = ', 
                    $this->dibi
                        ->select('MAX(topic_id)')
                        ->from($this->getTable())
                        ->where('[topic_forum_id] = %i', $forum_id)                        
                )
                ->fetch();
    }

    /**
     * @param int $forum_id
     * @param int $topic_time
     *
     * @return array|mixed
     */
    public function getNewerTopics($forum_id, $topic_time)
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
     * @param string $topic_name
     *
     * @return array
     */
    public function findByTopicName($topic_name)
    {
        return $this->getAllFluent()
            ->where('MATCH([topic_name]) AGAINST (%s IN BOOLEAN MODE)', $topic_name)
            ->fetchAll();
    }

    /**
     * @param int $forum_id
     *
     * @return Fluent
     */
    public function getFluentJoinedUsersByForum($forum_id)
    {
        return $this->getAllFluent()
            ->as('t')
            ->leftJoin(self::USERS_TABLE)
            ->as('u')
            ->on('[t.topic_user_id] = [u.user_id]')
            ->where('[t.topic_forum_id] = %i', $forum_id);
    }
    
    /**
     *
     * @param int $forum_id forum_id
     *
     * @return Row[]
     */
    public function getAllTopicsByForum($forum_id)
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
    public function getFLuentByUser($user_id)
    {
        return $this->getAllFluent()
                ->where('[topic_user_id] = %i', $user_id);
    }
    
    /**
     * 
     * @param int $topic_id
     * @param int $target_forum_id
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
        
        return $this->add(\Nette\Utils\ArrayHash::from($topic->toArray()));
    }
}
