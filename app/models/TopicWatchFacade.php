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
     * @var TopicsManager $topicsManager 
     */
    private $topicsManager;

    /**
     *
     * @var ForumsManager $forumsManager
     */
    private $forumsManager;
    
    /**
     *
     * @var PostsManager $postsManager
     */
    private $postsManager;

    /**
     *
     * @param UsersManager      $usersManager
     * @param TopicWatchManager $topicWatchManager
     */
    public function __construct(
        UsersManager $usersManager,
        TopicWatchManager $topicWatchManager,
        TopicsManager $topicsManager,
        ForumsManager $forumsManager,
        PostsManager $postsManager
    ) {
        $this->usersManager      = $usersManager;
        $this->topicWatchManager = $topicWatchManager;
        $this->topicsManager     = $topicsManager;
        $this->forumsManager     = $forumsManager;
        $this->postsManager      = $postsManager;
    }
    
    public function __destruct()
    {
        $this->usersManager      = null;
        $this->topicWatchManager = null;
        $this->topicsManager     = null;
        $this->forumsManager     = null;
        $this->postsManager      = null;
    }

        /**
     *
     * @param int $category_id
     */
    public function deleteByCategory($category_id)
    {
        $forums = $this->forumsManager->getAllByCategory($category_id);
        
        foreach ($forums as $forum) {
            $this->deleteByForum($forum->forum_id);
        }
    }

    /**
     * 
     * @param int $forum_id
     */
    public function deleteByForum($forum_id)
    {        
        $topics = $this->topicsManager->getAllByForum($forum_id);
        
        foreach ($topics as $topicDibi) {
            $topic = Entity\Topic::setFromRow($topicDibi);
            $this->deleteByTopic($topic);
        }
    }

    /**
     *
     * @param Entity\Topic $topic
     */
    public function deleteByTopic(Entity\Topic $topic)
    {
        $topicsWatches = $this->topicWatchManager->getAllByLeft($topic->getTopic_id());
        $user_ids      = [];

        foreach ($topicsWatches as $topicsWatch) {
            $user_ids[] = $topicsWatch->user_id;
        }

        if (count($user_ids)) {
            $this->usersManager->updateMulti(
                $user_ids,
                ArrayHash::from(['user_watch_count%sql' => 'user_watch_count - 1'])
            );
        }
        
        return $this->topicWatchManager->deleteByLeft($topic->topic_id);
    }
    
    /**
     *
     * @param Entity\Post $post
     */
    public function deleteByPost(Entity\Post $post)
    {       
        $postCount = $this->postsManager->getCountOfUsersByTopicId($post->getPost_topic_id());

        foreach ($postCount as $ps) {
            // check if user has there only one post so we can delete his topic watching
            // else he can still want to watch this topic
            if ($ps->post_count === 1 || $ps->post_count === 0) {
                $check = $this->topicWatchManager->fullCheck($post->getPost_topic_id(), $ps->post_user_id);

                if ($check) {
                    $this->topicWatchManager->fullDelete($post->getPost_topic_id(), $ps->post_user_id);                    
                    $this->usersManager->update($post->getPost_user_id(), ArrayHash::from(['user_watch_count%sql' => 'user_watch_count - 1']));
                }                                
            }
        }        
    }

    /**
     *
     * @param int $user_id
     */
    public function deleteByUser($user_id)
    {
        
    }
}
