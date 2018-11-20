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
    
    public function __destruct()
    {
        $this->topicFacade   = null;
        $this->topicsManager = null;
        $this->forumsManager = null;
    }

    /**
     * @param Entity\Forum $forum
     *
     * @return mixed
     */
    public function add(Entity\Forum $forum)
    {
        $forum_id = $this->forumsManager->getMptt()->add($forum->getForum_parent_id(), $forum->getForum_name());
        
        $forum->setForum_id($forum_id);
        
        $this->forumsManager->update($forum->getForum_id(), $forum->getArrayHash());
        
        return $forum->getForum_id();
    }
    
    /**
     * 
     * @param int $item_id
     * @param ArrayHash $item_data
     * 
     * @return bool
     */
    public function update($item_id, ArrayHash $item_data)
    {
        $forum = $this->forumsManager->getById($item_id);
        
        if ($forum->forum_parent_id !== $item_data->forum_parent_id) {
            $this->forumsManager->getMptt()->move($item_id, $item_data->forum_parent_id);
            
            unset($item_data->forum_parent_id);
        }
        
        return $this->forumsManager->update($item_id, $item_data);
    }

    /**
     * @param Entity\Forum $forum
     *
     * @return bool
     */
    public function delete(Entity\Forum $forum)
    {
        $forums = $this->forumsManager->getByParent($forum->getForum_id());

        foreach ($forums as $forumDibi) {
            $forum = Entity\Forum::setFromRow($forumDibi);
            $this->delete($forum);
        }

        $topics = $this->topicsManager->getAllByForum($forum->getForum_id());
        
        foreach ($topics as $topicDibi) {
            $topic = Entity\Topic::setFromRow($topicDibi);
            
            $this->topicFacade->delete($topic);
        }
 
        return $this->forumsManager->delete($forum->getForum_id());
    }
}
