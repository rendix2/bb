<?php

namespace App\Models;

use Nette\Utils\ArrayHash;

/**
 * Description of PostFacade
 *
 * @author rendi
 */
class PostFacade
{   
    /**
     *
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
     * @var TopicWatchManager $topicWatchManager
     */
    private $topicWatchManager;
    
    /**
     *
     * @var UsersManager $usersManager 
     */
    private $usersManager;
    
    /**
     *
     * @var ReportsManager $reportsManager
     */
    private $reportsManager;

    /**
     * 
     * @param \App\Models\PostsManager      $postsManager
     * @param \App\Models\TopicsManager     $topicsManager
     * @param \App\Models\TopicWatchManager $topicWatchManager
     */
    public function __construct(PostsManager $postsManager, TopicsManager $topicsManager, TopicWatchManager $topicWatchManager, UsersManager $usersManager, ReportsManager $reportsManager)
    {
        $this->postsManager      = $postsManager;
        $this->topicsManager     = $topicsManager;
        $this->topicWatchManager = $topicWatchManager;
        $this->usersManager      = $usersManager;
        $this->reportsManager    = $reportsManager;
    }
    
    public function add(ArrayHash $item_data)
    {
        $post_id  = $this->postsManager->add($item_data);
        $user_id  = $item_data->post_user_id;
        $forum_id = $item_data->post_forum_id;

        $this->topicsManager->update(
            $item_data->post_topic_id,
            ArrayHash::from(['topic_post_count%sql' => 'topic_post_count+1'])
        );

        $topicWatching = $this->topicWatchManager->fullCheck($item_data->post_topic_id, $user_id);

        $watch = [];

        if (!$topicWatching) {
            $this->topicWatchManager->add([$user_id], $item_data->post_topic_id);
            $watch = ['user_watch_count%sql' => 'user_watch_count + 1'];
        }

        $this->usersManager->update(
            $user_id,
            ArrayHash::from(['user_post_count%sql' => 'user_post_count + 1'] + $watch)
        );

        return $post_id;              
    }
    
    public function delete($item_id)
    {
        $post = $this->postsManager->getById($item_id);

        $this->usersManager->update(
            $post->post_user_id,
            ArrayHash::from(['user_post_count%sql' => 'user_post_count - 1'])
        );
        $this->topicsManager->update(
            $post->post_topic_id,
            ArrayHash::from(['topic_post_count%sql' => 'topic_post_count - 1'])
        );

        $postCount = $this->postsManager->getCountOfUsersByTopicId($post->post_topic_id);

        foreach ($postCount as $ps) {

            // check if user has there only one post so we can delete his topic watching
            // else he can still want to watch this topic
            if ($ps->post_count === 1 || $ps->post_count === 0) {
                $check = $this->topicWatchManager->fullCheck($post->post_topic_id, $ps->post_user_id);

                if ($check) {
                    $this->topicWatchManager->fullDelete($post->post_topic_id, $ps->post_user_id);
                }
            }
        }
        
        $this->reportsManager->deleteByPost($item_id);

        return $this->postsManager->delete($item_id);        
    }
}
