<?php

namespace App\Models\Traits;

use App\Models\TopicsManager;

/**
 * Description of TopicsTrait
 *
 * @author rendix2
 */
trait TopicsTrait
{
    
    /**
     * @var TopicsManager $topicsManager
     * @inject
     */
    public $topicsManager;  
    
    /**
     * 
     * @param int $topic_id
     * @param int $category_id
     * @param int $forum_id
     * 
     * @return \App\Models\Entity\Topic
     */
    public function checkTopicParam($topic_id, $category_id, $forum_id)
    {
        // topic check
        if (!isset($topic_id)) {
            $this->error('Topic param is not set.');
        }

        if (!is_numeric($topic_id)) {
            $this->error('Topic param is not numeric.');
        }

        $topicDibi = $this->topicsManager->getById($topic_id);
        
        if (!$topicDibi) {
            $this->error('Topic was not found.');
        }
        
        $topic = \App\Models\Entity\Topic::get($topicDibi);

        if ($topic->topic_category_id !== (int)$category_id) {
            $this->error('Category param does not match.');
        }

        if ($topic->topic_forum_id !== (int)$forum_id) {
            $this->error('Forum param does not match.');
        }

        if ($topic->topic_locked) {
            $this->error('Topic is locked.', IResponse::S403_FORBIDDEN);
        }
        
        return $topic;
    }
}
