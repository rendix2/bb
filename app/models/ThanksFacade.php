<?php

namespace App\Models;

use Dibi\Result;
use Nette\Utils\ArrayHash;

/**
 * Description of ThanksFacade
 *
 * @author rendix2
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
        UsersManager $usersManager,
        PostsManager $postsManager,
        TopicsManager $topicsManager,
        ForumsManager $forumsManager
    ) {
        $this->thanksManager = $thanksManager;
        $this->usersManager  = $usersManager;
        $this->postsManager  = $postsManager;
        $this->topicsManager = $topicsManager;
        $this->forumsManager = $forumsManager;
    }

    /**
     *
     * @param ArrayHash $item_data
     *
     * @return Result|int
     */
    public function add(Entity\Thank $thank)
    {
        $this->usersManager->update(
            $thank->thank_user_id,
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
        
        foreach ($forums as $forums) {
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
            $topic = Entity\Topic::get($topicDibi);
            
            $this->deleteByTopic($topic);
        }                
    }

    /**
     *
     * @param Entity\Topic $topic
     *
     * @return int
     */
    public function deleteByTopic(Entity\Topic $topic)
    {
        $thanks   = $this->thanksManager->getAllByTopic($topic->topic_id);
        $user_ids = [];

        foreach ($thanks as $thank) {
            $user_ids[] = $thank->thank_user_id;
        }

        if (count($user_ids)) {
            $this->usersManager->updateMulti(
                    $user_ids,
                    ArrayHash::from(['user_thank_count%sql' => 'user_thank_count - 1'])
            );
        }

        return $this->thanksManager->deleteByTopic($topic->topic_id);
    }

    /**
     *
     * @param Entity\Post $post
     *
     * @return bool
     */
    public function deleteByPost(Entity\Post $post)
    {        
        $count = $this->postsManager->getCountByUser($post->post_topic_id, $post->post_user_id);       

        if ($count === 1 || $count === 0) {
            $this->usersManager->update($post->post_user_id, ArrayHash::from(['user_thank_count%sql' => 'user_thank_count - 1']));

            return $this->thanksManager->deleteByUserAndTopic([$post->post_user_id], $post->post_topic_id);
        } else {
            return false;
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
