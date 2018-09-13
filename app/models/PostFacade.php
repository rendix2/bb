<?php

namespace App\Models;

use Dibi\Result;
use Nette\Utils\ArrayHash;

/**
 * Description of PostFacade
 *
 * @author rendix2
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
     * @var ForumsManager $forumsManager
     */
    private $forumsManager;
    
    /**
     *
     * @var PostsHistoryManager $postsHistoryManager
     */
    private $postsHistoryManager;
    
    /**
     *
     * @var ThanksManager $thanksManager
     */
    private $thanksManager;
    
    /**
     *
     * @var ThanksFacade $thanksFacade
     */
    private $thanksFacade;
    
    /**
     *
     * @var TopicWatchFacade $topicWatchFacade
     */
    private $topicWatchFacade;

    /**
     *
     * @param PostsManager        $postsManager
     * @param TopicsManager       $topicsManager
     * @param TopicWatchManager   $topicWatchManager
     * @param UsersManager        $usersManager
     * @param ReportsManager      $reportsManager
     * @param ForumsManager       $forumsManager
     * @param PostsHistoryManager $postsHistoryManager
     * @param ThanksManager       $thanksManager
     * @param ThanksFacade        $thanksFacade
     * @param TopicWatchFacade    $topicWatchFacade
     */
    public function __construct(
        PostsManager $postsManager,
        TopicsManager $topicsManager,
        TopicWatchManager $topicWatchManager,
        UsersManager $usersManager,
        ReportsManager $reportsManager,
        ForumsManager $forumsManager,
        PostsHistoryManager $postsHistoryManager,
        ThanksManager $thanksManager,
        ThanksFacade $thanksFacade,
        TopicWatchFacade $topicWatchFacade
    ) {
        $this->postsManager        = $postsManager;
        $this->topicsManager       = $topicsManager;
        $this->topicWatchManager   = $topicWatchManager;
        $this->usersManager        = $usersManager;
        $this->reportsManager      = $reportsManager;
        $this->forumsManager       = $forumsManager;
        $this->postsHistoryManager = $postsHistoryManager;
        $this->thanksManager       = $thanksManager;
        $this->thanksFacade        = $thanksFacade;
        $this->topicWatchFacade    = $topicWatchFacade;
    }

    /**
     * @param ArrayHash $item_data
     *
     * @return Result|int
     */
    public function add(ArrayHash $item_data)
    {
        $post_id  = $this->postsManager->add($item_data);
        $user_id  = $item_data->post_user_id;
        $forum_id = $item_data->post_forum_id;

        $this->topicsManager->update(
            $item_data->post_topic_id,
            ArrayHash::from([
                'topic_post_count%sql' => 'topic_post_count + 1',
                'topic_last_user_id'   => $user_id,
                'topic_last_post_id'   => $post_id
                ])
        );

        $topicWatching = $this->topicWatchManager->fullCheck($item_data->post_topic_id, $user_id);

        $watch = [];

        if (!$topicWatching) {
            $this->topicWatchManager->add([$user_id], $item_data->post_topic_id);
            $watch = ['user_watch_count%sql' => 'user_watch_count + 1'];
        }

        $this->postsHistoryManager->add(ArrayHash::from([
                'post_id'           => $post_id,
                'post_user_id'      => $user_id,
                'post_title'        => $item_data->post_title,
                'post_text'         => $item_data->post_text,
                'post_history_time' => time()
            ]));
        $this->usersManager->update($user_id, ArrayHash::from([
                'user_post_count%sql' => 'user_post_count + 1',
                'user_last_post_time' => time()
            ] + $watch));
        
        $this->forumsManager->update($forum_id, ArrayHash::from(['forum_post_count%sql' => 'forum_post_count + 1']));

        return $post_id;
    }

    /**
     * @param int       $item_id
     * @param ArrayHash $item_data
     *
     * @return bool
     */
    public function update($item_id, ArrayHash $item_data)
    {
        $update = $this->postsManager->update($item_id, $item_data);
        $add = $this->postsHistoryManager->add(ArrayHash::from([
                'post_id'           => $item_id,
                'post_user_id'      => $item_data->post_user_id,
                'post_title'        => $item_data->post_title,
                'post_text'         => $item_data->post_text,
                'post_history_time' => time()
            ]));

        return $update && $add;
    }

    /**
     * @param int $item_id
     *
     * @return Result|int
     */
    public function delete($item_id)
    {
        $post  = $this->postsManager->getById($item_id);
        $topic = $this->topicsManager->getById($post->post_topic_id);

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
        $this->forumsManager->update(
            $post->post_forum_id,
            ArrayHash::from(['forum_post_count%sql' => 'forum_post_count - 1'])
        );
        
        // recount last post info
        $res = $this->postsManager->delete($item_id);
        
        $this->postsHistoryManager->deleteByPost($item_id);
        
        // last post
        if ($topic->topic_last_post_id === (int)$item_id && $topic->topic_first_post_id !== (int)$item_id) {
            $last_post = $this->postsManager->getLastByTopic($post->post_topic_id);

            $this->topicsManager->update($post->post_topic_id, ArrayHash::from([
                'topic_last_post_id' => $last_post->post_id,
                'topic_last_user_id' => $last_post->post_user_id
            ]));
        } elseif ($topic->topic_first_post_id === (int)$item_id && $topic->topic_last_post_id !== (int)$item_id) {
            $first_post = $this->postsManager->getFirstByTopic($post->post_topic_id);

            $this->topicsManager->update($post->post_topic_id, ArrayHash::from([
                'topic_first_post_id' => $first_post->post_id,
                'topic_first_user_id' => $first_post->post_user_id
            ]));
        } elseif ($topic->topic_last_post_id === $topic->topic_first_post_id && $topic->topic_first_post_id === (int)$item_id) {
            $this->forumsManager->update($post->post_forum_id, ArrayHash::from(['forum_topic_count%sql' => 'forum_topic_count - 1']));
            $this->thanksFacade->deleteByTopic($topic->topic_id);
            $this->reportsManager->deleteByTopic($topic->topic_id);
            
            $userUpdate = ['user_topic_count%sql' => 'user_topic_count - 1'];
            
            $watching = $this->topicWatchManager->fullCheck($topic->topic_id, $topic->topic_user_id);
            
            if (!$watching) {
                $userUpdate['user_watch_count%sql'] = ['user_watch_count - 1'];
            }

            $this->usersManager->update($topic->topic_user_id, ArrayHash::from($userUpdate));
            $this->topicsManager->delete($topic->topic_id);

            return 2;
        }
        
        $lastPostOfUser = $this->postsManager->getLastByUser($post->post_user_id);

        if ($lastPostOfUser) {
            $this->usersManager->update(
                $post->post_user_id,
                ArrayHash::from(['user_last_post_time' => $lastPostOfUser->post_add_time])
            );
        } else {
            $this->usersManager->update(
                $post->post_user_id,
                ArrayHash::from(['user_last_post_time' => 0])
            );
        }
        
        return $res;
    }
    
    /**
     * 
     * @param int $post_id
     * @param int $target_topic_id
     * 
     * @return boolean
     */
    public function move($post_id, $target_topic_id)
    {
        $post = $this->postsManager->getById($post_id);
       
        if (!$post) {
            return false;
        }     
        
        $target_topic = $this->topicsManager->getById($target_topic_id);
        
        if (!$target_topic) {
            return false;
        }
        
        $source_topic_id = $post->post_topic_id;
        $source_forum_id = $post->post_forum_id;        
                
        $target_forum_id = $target_topic->topic_forum_id;
       
        if ($source_topic_id !== $target_topic_id) {            
            $this->topicsManager->update($source_topic_id, ArrayHash::from(['topic_post_count%sql' => 'topic_post_count - 1']));
            $this->topicsManager->update($target_topic_id, ArrayHash::from(['topic_post_count%sql' => 'topic_post_count + 1']));
        }
        
        if ($source_forum_id!== $target_forum_id) {
            $this->forumsManager->update($source_forum_id, ArrayHash::from(['forum_post_count%sql' => 'forum_post_count - 1']));
            $this->forumsManager->update($target_forum_id, ArrayHash::from(['forum_post_count%sql' => 'forum_post_count + 1']));
        }
    }
}
