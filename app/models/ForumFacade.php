<?php

namespace App\Models;

/**
 * Description of ForumFacade
 *
 * @author rendix2
 */
class ForumFacade
{
    /**
     *
     * @var ForumsManager $forumsManager
     */
    private $forumsManager;
    
    /**
     *
     * @var TopicFacade $topicFacade
     */
    private $topicFacade;
    
    /**
     *
     * @var TopicsManager $topicsManager
     */
    private $topicsManager;

    /**
     * 
     * @param \App\Models\ForumsManager $forumsManager
     * @param \App\Models\TopicFacade   $topicFacade
     * @param \App\Models\TopicsManager $topicsManager
     */
    public function __construct(ForumsManager $forumsManager, TopicFacade $topicFacade, TopicsManager $topicsManager)
    {
        $this->forumsManager = $forumsManager;
        $this->topicFacade   = $topicFacade;
        $this->topicsManager = $topicsManager;
    }
    
    /**
     * @param int $item_id
     * 
     * @return bool
     */
    public function delete($item_id)
    {        
        $topics = $this->topicsManager->getAllTopicsByForum($item_id);
        
        foreach ($topics as $topic) {
            $this->topicFacade->delete($topic->topic_id);            
        }
 
        return $this->forumsManager->delete($item_id);
    }    
}
