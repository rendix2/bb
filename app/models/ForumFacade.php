<?php

namespace App\Models;

use Dibi\Connection;
use Zebra_Mptt;

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
     * @var Zebra_Mptt $mptt
     */
    public $mptt;

    /**
     *
     * @param ForumsManager $forumsManager
     * @param TopicFacade   $topicFacade
     * @param TopicsManager $topicsManager
     */
    public function __construct(Connection $dibi, ForumsManager $forumsManager, TopicFacade $topicFacade, TopicsManager $topicsManager)
    {
        $this->forumsManager = $forumsManager;
        $this->topicFacade   = $topicFacade;
        $this->topicsManager = $topicsManager;
        
        $this->mptt = new Zebra_Mptt(
            $dibi,
            $this->forumsManager->getTable(),
            $this->forumsManager->getPrimaryKey(),
            'forum_name',
            'forum_left',
            'forum_right',
            'forum_parent_id'
        );
    }
    
    public function add(\Nette\Utils\ArrayHash $item_data)
    {    
        $forum_id = $this->mptt->add($item_data->forum_parent_id, $item_data->forum_name);
        
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
        $topics = $this->topicsManager->getAllTopicsByForum($item_id);
        
        foreach ($topics as $topic) {
            $this->topicFacade->delete($topic->topic_id);
        }
 
        return $this->forumsManager->delete($item_id);
    }
}
