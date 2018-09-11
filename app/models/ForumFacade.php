<?php

namespace App\Models;

use Nette\Utils\ArrayHash;

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
     * @param ForumsManager $forumsManager
     * @param TopicFacade   $topicFacade
     * @param TopicsManager $topicsManager
     */
    public function __construct(
            ForumsManager $forumsManager,
            TopicFacade $topicFacade,
            TopicsManager $topicsManager
    ) {
        $this->forumsManager = $forumsManager;
        $this->topicFacade   = $topicFacade;
        $this->topicsManager = $topicsManager;
    }
    
    public function add(ArrayHash $item_data)
    {
        $forum_id = $this->forumsManager->getMptt()->add($item_data->forum_parent_id, $item_data->forum_name);
        
        $this->forumsManager->update($forum_id, $item_data);
        
        return $forum_id;
    }

    /**
     * @param int $item_id
     *
     * @return bool
     */
    public function delete($item_id)
    {
        $topics = $this->topicsManager->getAllByForum($item_id);
        
        foreach ($topics as $topic) {
            $this->topicFacade->delete($topic->topic_id);
        }
 
        return $this->forumsManager->delete($item_id);
    }
}
