<?php

namespace App\Models\Traits;

use App\Models\Entity\TopicEntity;
use App\Models\TopicsManager;
use Nette\Http\IResponse;

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
     * @return TopicEntity
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
        
        $topic = TopicEntity::setFromRow($topicDibi);

        if ($topic->getTopic_category_id() !== (int)$category_id) {
            $this->error('Category param does not match.');
        }

        if ($topic->getTopic_forum_id() !== (int)$forum_id) {
            $this->error('Forum param does not match.');
        }

        if ($topic->getTopic_locked()) {
            $this->error('Topic is locked.', IResponse::S403_FORBIDDEN);
        }
        
        return $topic;
    }
}
