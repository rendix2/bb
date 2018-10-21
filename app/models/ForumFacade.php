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
        $forum->forum_id = $this->forumsManager->getMptt()->add($forum->forum_parent_id, $forum->forum_name);        
        
        $this->forumsManager->update($forum->forum_id, $forum->getArrayHash());
        
        return $forum->forum_id;
    }
    
    /**
     * 
     * @param type $item_id
     * @param ArrayHash $item_data
     * 
     * @return type
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
        $forums = $this->forumsManager->getByParent($forum->forum_id);

        foreach ($forums as $forumDibi) {
            $forum = Entity\Forum::get($forumDibi);
            $this->delete($forum);
        }

        $topics = $this->topicsManager->getAllByForum($forum->forum_id);
        
        foreach ($topics as $topicDibi) {
            $topic = Entity\Topic::get($topicDibi);
            
            $this->topicFacade->delete($topic);
        }
 
        return $this->forumsManager->delete($forum->forum_id);
    }
}
