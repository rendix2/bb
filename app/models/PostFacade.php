<?php

namespace App\Models;

use Dibi\Result;
use Nette\Utils\ArrayHash;
use App\Settings\TopicsSetting;

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
     * @var PollsFacade $pollsFacade
     */
    private $pollsFacade;
    
    /**
     *
     * @var TopicsSetting $topicSettings
     */
    private $topicSettings;

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
     * @param PollsFacade         $pollsFacade
     * @param TopicsSetting       $topicSettings
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
        TopicWatchFacade $topicWatchFacade,
        PollsFacade $pollsFacade,
        TopicsSetting $topicSettings
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
        $this->pollsFacade         = $pollsFacade;
        $this->topicSettings       = $topicSettings;
    }
    
    public function __destruct()
    {
        $this->postsManager        = null;
        $this->topicsManager       = null;
        $this->topicWatchManager   = null;
        $this->usersManager        = null;
        $this->reportsManager      = null;
        $this->forumsManager       = null;
        $this->postsHistoryManager = null;
        $this->thanksManager       = null;
        $this->thanksFacade        = null;
        $this->topicWatchFacade    = null;
        $this->topicSettings       = null;
    }

    /**
     * @param ArrayHash $post
     *
     * @return Result|int
     */
    public function add(Entity\Post $post)
    {
        $post_id  = $this->postsManager->add($post->getArrayHash());
        $user_id  = $post->getPost_user_id();
        $forum_id = $post->getPost_forum_id();
        $topic_id = $post->getPost_topic_id();
        
        $topicDibi = $this->topicsManager->getById($topic_id);
        $topic     = Entity\Topic::setFromRow($topicDibi);

        $this->topicsManager->update(
            $topic_id,
            ArrayHash::from([
                'topic_post_count%sql' => 'topic_post_count + 1',
                'topic_last_user_id'   => $user_id,
                'topic_last_post_id'   => $post_id,
                'topic_page_count'     => ceil(($topic->getTopic_post_count() + 1) / $this->topicSettings->get()['pagination']['itemsPerPage'])
                ])
        );

        $topicWatching = $this->topicWatchManager->fullCheck($topic_id, $user_id);

        $watch = [];

        if (!$topicWatching) {
            $this->topicWatchManager->add([$user_id], $topic_id);
            $watch = ['user_watch_count%sql' => 'user_watch_count + 1'];
        }

        $this->postsHistoryManager->add(ArrayHash::from([
                'post_id'           => $post_id,
                'post_user_id'      => $user_id,
                'post_title'        => $post->getPost_title(),
                'post_text'         => $post->getPost_text(),
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
    public function update(Entity\Post $post)
    {
        //$myPost = clone $post;
        //unset($myPost->post_id);

        $update = $this->postsManager->update($post->getPost_id(), $post->getArrayHash());
        $add    = $this->postsHistoryManager->add(ArrayHash::from([
                'post_id'           => $post->getPost_id(),
                'post_user_id'      => $post->getPost_user_id(),
                'post_title'        => $post->getPost_title(),
                'post_text'         => $post->getPost_text(),
                'post_history_time' => time()
            ]));

        return $update && $add;
    }

    /**
     * @param int $item_id
     *
     * @return Result|int
     */
    public function delete(Entity\Topic $topic, Entity\Post $post)
    {
        $this->usersManager->update(
            $post->getPost_user_id(),
            ArrayHash::from(['user_post_count%sql' => 'user_post_count - 1'])
        );
        $this->topicsManager->update(
            $post->getPost_topic_id(),
            ArrayHash::from([
                'topic_post_count%sql' => 'topic_post_count - 1',
                'topic_page_count'     => ceil(($topic->getTopic_post_count() - 1) / $this->topicSettings->get()['pagination']['itemsPerPage'])
                ])
        );

        $this->thanksFacade->deleteByPost($post);
        $this->postsHistoryManager->deleteByPost($post->getPost_id());
        $this->topicWatchFacade->deleteByPost($post);
        $this->reportsManager->deleteByPost($post->getPost_id());
        $this->forumsManager->update(
            $post->getPost_forum_id(),
            ArrayHash::from(['forum_post_count%sql' => 'forum_post_count - 1'])
        );
        
        // recount last post info
        $res = $this->postsManager->delete($post->getPost_id());        
                
        // last post
        if ($topic->getTopic_last_post_id() === (int)$post->getPost_id() && $topic->getTopic_first_post_id() !== (int)$post->getPost_id()) {
            $last_post = $this->postsManager->getLastByTopic($post->post_topic_id);

            if ($last_post) {
                $this->topicsManager->update($post->getPost_topic_id(), ArrayHash::from([
                    'topic_last_post_id' => $last_post->post_id,
                    'topic_last_user_id' => $last_post->post_user_id
                ]));                
            }
        } elseif ($topic->getTopic_first_post_id() === (int)$post->getPost_id() && $topic->getTopic_last_post_id() !== (int)$post->getPost_id()) {
            $first_post = $this->postsManager->getFirstByTopic($post->getPost_topic_id());
            
            if ($first_post) {
                $this->topicsManager->update($post->getPost_topic_id(), ArrayHash::from([
                    'topic_first_post_id' => $first_post->post_id,
                    'topic_first_user_id' => $first_post->post_user_id
                ]));                
            }
        } elseif ($topic->getTopic_last_post_id() === $topic->getTopic_first_post_id() && $topic->getTopic_first_post_id() === (int)$post->getPost_id()) {
            $this->forumsManager->update($post->getPost_forum_id(), ArrayHash::from(['forum_topic_count%sql' => 'forum_topic_count - 1']));
            $this->thanksFacade->deleteByTopic($topic);
            $this->reportsManager->deleteByTopic($topic->getTopic_id());
            $this->topicWatchFacade->deleteByTopic($topic);
            $this->usersManager->update($topic->getTopic_user_id(), ArrayHash::from(['user_topic_count%sql' => 'user_topic_count - 1']));
            
            if ($topic->getPoll()) {
                $this->pollsFacade->delete($topic->getPoll());
            }
            
            $this->topicsManager->delete($topic->getTopic_id());

            return 2;
        }
        
        $lastPostOfUser = $this->postsManager->getLastByUser($post->getPost_user_id());

        if ($lastPostOfUser) {
            $this->usersManager->update(
                $post->getPost_user_id(),
                ArrayHash::from(['user_last_post_time' => $lastPostOfUser->post_add_time])
            );
        } else {
            $this->usersManager->update(
                $post->getPost_user_id(),
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
        
        $this->reportsManager->updateByPost($post_id, ArrayHash::from(['report_topic_id' => $target_topic_id]));
        
        return $this->postsManager->update($post_id, ArrayHash::from(['post_topic_id' => $target_topic_id]));
    }
}
