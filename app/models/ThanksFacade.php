<?php

namespace App\Models;

use App\Models\Entity\PostEntity;
use App\Models\Entity\ThankEntity;
use App\Models\Entity\TopicEntity;
use Dibi\Result;
use Nette\Utils\ArrayHash;

/**
 * Description of ThanksFacade
 *
 * @author rendix2
 * @package App\Models
 */
class ThanksFacade
{
    /**
     * @var ThanksManager $thanksManager
     */
    private $thanksManager;
    
    /**
     * @var UsersManager $usersManager
     */
    private $usersManager;

    /**
     * @var PostsManager $postsManager
     */
    private $postsManager;
    
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
     * @param ThanksManager $thanksManager
     * @param UsersManager  $usersManager
     * @param PostsManager  $postsManager
     * @param TopicsManager $topicsManager
     * @param ForumsManager $forumsManager
     */
    public function __construct(
        ThanksManager $thanksManager,
        UsersManager  $usersManager,
        PostsManager  $postsManager,
        TopicsManager $topicsManager,
        ForumsManager $forumsManager
    ) {
        $this->thanksManager = $thanksManager;
        $this->usersManager  = $usersManager;
        $this->postsManager  = $postsManager;
        $this->topicsManager = $topicsManager;
        $this->forumsManager = $forumsManager;
    }
    
    public function __destruct()
    {
        $this->thanksManager = null;
        $this->usersManager  = null;
        $this->postsManager  = null;
        $this->topicsManager = null;
        $this->forumsManager = null;
    }

    /**
     *
     * @param ThankEntity $thank
     *
     * @return Result|int
     */
    public function add(ThankEntity $thank)
    {
        $this->usersManager->update(
            $thank->getThank_user_id(),
            ArrayHash::from(['user_thank_count%sql' => 'user_thank_count + 1'])
        );

        return $this->thanksManager->add($thank->getArrayHash());
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
     * @return int
     */
    public function deleteByTopic(TopicEntity $topic)
    {
        $thanks   = $this->thanksManager->getAllByTopic($topic->getTopic_id());
        $user_ids = \App\Utils::arrayObjectColumn($thanks, 'thank_user_id');

        if (count($user_ids)) {
            $this->usersManager->updateMulti(
                $user_ids,
                ArrayHash::from(['user_thank_count%sql' => 'user_thank_count - 1'])
            );
        }

        return $this->thanksManager->deleteByTopic($topic->getTopic_id());
    }

    /**
     *
     * @param PostEntity $post
     *
     * @return bool
     */
    public function deleteByPost(PostEntity $post)
    {
        $count = $this->postsManager->getCountByUser($post->getPost_topic_id(), $post->getPost_user_id());

        if ($count === 1 || $count === 0) {
            $this->usersManager->update($post->getPost_user_id(), ArrayHash::from(['user_thank_count%sql' => 'user_thank_count - 1']));

            return $this->thanksManager->deleteByUsersAndTopic([$post->getPost_user_id()], $post->getPost_topic_id());
        } else {
            return false;
        }
    }
}
