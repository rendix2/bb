<?php

namespace App\Models;

use Nette\Utils\ArrayHash;

/**
 * Description of TopicWatchFacade
 *
 * @author rendi
 */
class TopicWatchFacade
{

    /**
     *
     * @var UsersManager $usersManager
     */
    private $usersManager;
    
    /**
     * @var TopicWatchManager $topicWatchManager
     */
    private $topicWatchManager;
    
    /**
     *
     * @param UsersManager      $usersManager
     * @param TopicWatchManager $topicWatchManager
     */
    public function __construct(UsersManager $usersManager, TopicWatchManager $topicWatchManager)
    {
        $this->usersManager      = $usersManager;
        $this->topicWatchManager = $topicWatchManager;
    }
    
    /**
     *
     * @param int $category_id
     */
    public function deleteByCategory($category_id)
    {
        
    }

    /**
     * 
     * @param int $forum_id
     */
    public function deleteByForum($forum_id)
    {
    }

    /**
     *
     * @param int $topic_id
     */
    public function deleteByTopic($topic_id)
    {
        $topicsWatches = $this->topicWatchManager->getAllByLeft($topic_id);
        $user_ids      = [];

        foreach ($topicsWatches as $topicsWatch) {
            $user_ids[] = $topicsWatch->user_id;
        }

        $this->usersManager->updateMulti(
            $user_ids,
            ArrayHash::from(['user_watch_count%sql' => 'user_watch_count - 1'])
        );
    }
    
    /**
     *
     * @param int $post_id
     */
    public function deleteByPost($post_id)
    {
    }

    /**
     *
     * @param int $user_id
     */
    public function deleteByUser($user_id)
    {
        
    }
}
