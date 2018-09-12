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

    /**
     * @param ArrayHash $item_data
     *
     * @return mixed
     */
    public function add(ArrayHash $item_data)
    {
        $forum_id = $this->forumsManager->getMptt()->add($item_data->forum_parent_id, $item_data->forum_name);
        
        $this->forumsManager->update($forum_id, $item_data);
        
        return $forum_id;
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
            
            unset($item_data->$forum_parent_id);
        }
        
        return $this->forumsManager->update($item_id, $item_data);
    }    

    /**
     * @param int $item_id
     *
     * @return bool
     */
    public function delete($item_id)
    {
        $forums = $this->forumsManager->getByParent($item_id);

        foreach ($forums as $forum) {
            $this->delete($forum->forum_id);
        }

        $topics = $this->topicsManager->getAllByForum($item_id);
        
        foreach ($topics as $topic) {
            $this->topicFacade->delete($topic->topic_id);
        }
 
        return $this->forumsManager->delete($item_id);
    }
}
