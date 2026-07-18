<?php

namespace App\Models;

use App\Models\Entity\PostEntity;
use App\Models\Entity\TopicEntity;
use App\Utils;
use Dibi\Result;
use Nette\Utils\ArrayHash;

/**
 * Description of TopicWatchFacade
 *
 * @author rendix2
 * @package App\Models
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
     * @var TopicManager $topicsManager
     */
    private $topicsManager;

    /**
     *
     * @var ForumManager $forumsManager
     */
    private $forumsManager;
    
    /**
     *
     * @var PostManager $postsManager
     */
    private $postsManager;


    /**
     * TopicWatchFacade constructor.
     *
     * @param UsersManager      $usersManager
     * @param TopicWatchManager $topicWatchManager
     * @param TopicManager     $topicsManager
     * @param ForumManager     $forumsManager
     * @param PostManager      $postsManager
     */
    public function __construct(
        UsersManager      $usersManager,
        TopicWatchManager $topicWatchManager,
        TopicManager     $topicsManager,
        ForumManager     $forumsManager,
        PostManager      $postsManager
    ) {
        $this->usersManager      = $usersManager;
        $this->topicWatchManager = $topicWatchManager;
        $this->topicsManager     = $topicsManager;
        $this->forumsManager     = $forumsManager;
        $this->postsManager      = $postsManager;
    }
    
    /**
     * TopicWatchFacade destructor.
     */
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
            $topic = TopicEntity::setFromRow($topicDibi);
            $this->deleteByTopic($topic);
        }
    }

    /**
     *
     * @param TopicEntity $topic
     *
     * @return Result|int
     */
    public function deleteByTopic(TopicEntity $topic)
    {
        $topicsWatches = $this->topicWatchManager->getAllByLeft($topic->getTopic_id());
        $user_ids      = Utils::arrayObjectColumn($topicsWatches, 'user_id');

        if (count($user_ids)) {
            $this->usersManager->updateMulti(
                $user_ids,
                ArrayHash::from(['user_watch_count%sql' => 'user_watch_count - 1'])
            );
        }
        
        return $this->topicWatchManager->deleteByLeft($topic->getTopic_id());
    }

    /**
     *
     * @param PostEntity $post
     */
    public function deleteByPost(PostEntity $post)
    {
        $postCount = $this->postsManager->getCountOfUsersByTopicId($post->getPost_topic_id());

        foreach ($postCount as $ps) {
            // check if user has there only one post so we can delete his topic watching
            // else he can still want to watch this topic
            if ($ps->post_count === 1 || $ps->post_count === 0) {
                $check = $this->topicWatchManager->fullCheck($post->getPost_topic_id(), $ps->post_user_id);

                if ($check) {
                    $this->topicWatchManager->delete($post->getPost_topic_id(), $ps->post_user_id);
                    $this->usersManager->update(
                        $post->getPost_user_id(),
                        ArrayHash::from(['user_watch_count%sql' => 'user_watch_count - 1'])
                    );
                }
            }
        }
    }
}
