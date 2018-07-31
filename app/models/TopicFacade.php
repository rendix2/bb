<?php

namespace App\Models;

use Dibi\Result;
use Nette\Utils\ArrayHash;

/**
 * Description of TopicFacade
 *
 * @author rendi
 */
class TopicFacade
{
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
     * @var PostsManager $postsManager
     */
    private $postsManager;
    
    /**
     *
     * @var UsersManager $usersManager
     */
    private $usersManager;

    /**
     *
     * @var ThanksManager $thanksManager
     */
    private $thanksManager;
    
    /**
     *
     * @var ForumsManager $forumsManager
     */
    private $forumsManager;
    
    /**
     *
     * @var PostFacade $postFacade
     */
    private $postFacade;

    /**
     *
     * @param TopicsManager     $topicsManager
     * @param TopicWatchManager $topicWatchManager
     * @param PostsManager      $postsManager
     * @param UsersManager      $usersManager
     * @param ThanksManager     $thanksManager
     * @param ForumsManager     $forumsManager
     * @param PostFacade        $postFacade
     */
    public function __construct(
        TopicsManager $topicsManager,
        TopicWatchManager $topicWatchManager,
        PostsManager $postsManager,
        UsersManager $usersManager,
        ThanksManager $thanksManager,
        ForumsManager $forumsManager,
        PostFacade $postFacade
    ) {
        $this->topicsManager     = $topicsManager;
        $this->topicWatchManager = $topicWatchManager;
        $this->postsManager      = $postsManager;
        $this->usersManager      = $usersManager;
        $this->thanksManager     = $thanksManager;
        $this->postFacade        = $postFacade;
        $this->forumsManager     = $forumsManager;
    }

    /**
     *
     * @param ArrayHash $item_data
     *
     * @return Result|int
     */
    public function add(ArrayHash $item_data)
    {
        $values = clone $item_data;

        $values->topic_name          = $item_data->post_title;
        $values->topic_user_id       = $item_data->post_user_id;
        $values->topic_forum_id      = $item_data->post_forum_id;
        $values->topic_add_time      = $item_data->post_add_time;
        $values->topic_first_user_id = $item_data->post_user_id;
        $values->topic_last_user_id  = $item_data->post_user_id;
        //$values->post_add_user_ip = $item_data->post_add_user_ip;

        unset(
            $values->post_title,
            $values->post_text,
            $values->post_add_time,
            $values->post_user_id,
            $values->post_forum_id,
            $values->post_add_user_ip
        );

        $topic_id = $this->topicsManager->add($values);

        $this->topicWatchManager->add([$values->topic_user_id], $topic_id);

        $item_data->post_topic_id = $topic_id;
        $post_id = $this->postFacade->add($item_data);
        
        $this->topicsManager->update($topic_id, ArrayHash::from(['topic_first_post_id' => $post_id, 'topic_last_post_id' => $post_id]));
        $this->usersManager->update($values->topic_user_id, ArrayHash::from(
            [
                'user_topic_count%sql' => 'user_topic_count + 1',
                'user_watch_count%sql' => 'user_watch_count + 1'
            ]
        ));
        
        $this->forumsManager->update($values->topic_forum_id, ArrayHash::from(['forum_topic_count%sql' => 'forum_topic_count + 1']));

        return $topic_id;
    }

    /**
     *
     * @param type $item_id
     *
     * @return Result|int
     */
    public function delete($item_id)
    {
        $topic = $this->topicsManager->getById($item_id);

        // delete thanks
        $thanks = $this->thanksManager->getThanksByTopic($item_id);

        foreach ($thanks as $thank) {
            $this->usersManager->update(
                $thank->thank_user_id,
                ArrayHash::from(['user_thank_count%sql' => 'user_thank_count - 1'])
            );
        }

        $this->thanksManager->deleteByTopic($item_id);
        // delete thanks
        
        // topics watches
        $topicsWatches = $this->topicWatchManager->getAllByLeft($item_id);
                
        foreach ($topicsWatches as $topicsWatch) {
            $this->usersManager->update($topicsWatch->user_id, ArrayHash::from(['user_watch_count%sql' => 'user_watch_count - 1']));
        }
        // topics watches

        $this->usersManager
                ->update($topic->topic_user_id, ArrayHash::from(['user_topic_count%sql' => 'user_topic_count - 1']));

        $posts = $this->postsManager
                ->getByTopic($item_id)
                ->fetchAll();

        foreach ($posts as $post) {
            $this->postFacade->delete($post->post_id);
        }
        
        $this->forumsManager->update($topic->topic_forum_id, ArrayHash::from(['forum_topic_count%sql' => 'forum_topic_count - 1']));

        return $this->topicsManager->delete($item_id);
    }

    /**
     * @param $item_id
     */
    public function copy($item_id)
    {
    }
}
