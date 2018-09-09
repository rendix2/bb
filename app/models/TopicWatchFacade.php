<?php

namespace App\Models;

use App\Models\UsersManager;
use App\Models\TopicWatchManager;

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
