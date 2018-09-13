<?php

namespace App\Models;

use Dibi\Result;
use Nette\Utils\ArrayHash;

/**
 * Description of TopicFacade
 *
 * @author rendix2
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
     * @var ReportsManager $reportsManager
     */
    private $reportsManager;
    
    /**
     *
     * @var TopicWatchFacade $topicWatchFacade
     */
    private $topicWatchFacade;
    
    /**
     *
     * @var ThanksFacade $thanksFacade
     */
    private $thanksFacade;

    /**
     * @var ReportFacade $reportFacade
     */
    private $reportFacade;

    /**
     *
     * @param TopicsManager     $topicsManager
     * @param TopicWatchManager $topicWatchManager
     * @param PostsManager      $postsManager
     * @param UsersManager      $usersManager
     * @param ThanksManager     $thanksManager
     * @param ForumsManager     $forumsManager
     * @param PostFacade        $postFacade
     * @param ReportsManager    $reportsManager
     * @param TopicWatchFacade  $topicWatchFacade
     * @param ThanksFacade      $thanksFacade
     * @param ReportFacade      $reportFacade
     */
    public function __construct(
        TopicsManager $topicsManager,
        TopicWatchManager $topicWatchManager,
        PostsManager $postsManager,
        UsersManager $usersManager,
        ThanksManager $thanksManager,
        ForumsManager $forumsManager,
        PostFacade $postFacade,
        ReportsManager $reportsManager,
        TopicWatchFacade $topicWatchFacade,
        ThanksFacade $thanksFacade,
        ReportFacade $reportFacade
    ) {
        $this->topicsManager     = $topicsManager;
        $this->topicWatchManager = $topicWatchManager;
        $this->postsManager      = $postsManager;
        $this->usersManager      = $usersManager;
        $this->thanksManager     = $thanksManager;
        $this->postFacade        = $postFacade;
        $this->forumsManager     = $forumsManager;
        $this->reportsManager    = $reportsManager;
        $this->topicWatchFacade  = $topicWatchFacade;
        $this->thanksFacade      = $thanksFacade;
        $this->reportFacade      = $reportFacade;
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

        $this->topicsManager->update(
            $topic_id,
            ArrayHash::from(['topic_first_post_id' => $post_id, 'topic_last_post_id' => $post_id])
        );

        $this->usersManager->update($values->topic_user_id, ArrayHash::from(
            [
                'user_topic_count%sql' => 'user_topic_count + 1',
                'user_watch_count%sql' => 'user_watch_count + 1'
            ]
        ));

        $this->forumsManager->update(
            $values->topic_forum_id,
            ArrayHash::from(['forum_topic_count%sql' => 'forum_topic_count + 1'])
        );

        return $topic_id;
    }

    /**
     *
     * @param int $item_id
     *
     * @return Result|int
     */
    public function delete($item_id)
    {
        $topic = $this->topicsManager->getById($item_id);

        $this->thanksFacade->deleteByTopic($item_id);
        $this->topicWatchFacade->deleteByTopic($item_id);
        
        $this->usersManager
                ->update($topic->topic_user_id, ArrayHash::from(['user_topic_count%sql' => 'user_topic_count - 1']));

        $posts = $this->postsManager
                ->getFluentByTopicJoinedUser($item_id)
                ->fetchAll();

        foreach ($posts as $post) {
            $this->postFacade->delete($post->post_id);
        }

        $this->forumsManager->update(
            $topic->topic_forum_id,
            ArrayHash::from(['forum_topic_count%sql' => 'forum_topic_count - 1'])
        );
        
        $this->reportFacade->deleteByTopic($item_id);

        return $this->topicsManager->delete($item_id);
    }

    /**
     * @param int      $topic_id
     * @param int|null $target_forum_id
     *
     * @return int
     */
    public function copy($topic_id, $target_forum_id = null)
    {
        $posts        = $this->postsManager->getByTopic($topic_id);
        $new_topic_id = $this->topicsManager->copy($topic_id, $target_forum_id);

        foreach ($posts as $post) {
            $this->postsManager->copy($post->post_id, $new_topic_id);
        }

        return $new_topic_id;
    }

    /**
     * moves topic to another forum
     *
     * @param int $topic_id
     * @param int $target_forum_id
     */
    public function move($topic_id, $target_forum_id)
    {
        $topic = $this->topicsManager->getById($topic_id);
        
        $source_forum_id = $topic->topic_forum_id;
        
        if (!$topic) {
            return false;
        }
        
        if ($source_forum_id === $target_forum_id) {
            return false;
        }
        
        $post_ids = [];
        $posts    = $this->postsManager->getByTopic($topic_id);

        $this->forumsManager->update($source_forum_id, ArrayHash::from(['forum_topic_count%sql' => 'forum_topic_count - 1', 'forum_post_count%sql' => 'forum_post_count - ' . $topic->topic_post_count]));
        $this->forumsManager->update($target_forum_id, ArrayHash::from(['forum_topic_count%sql' => 'forum_topic_count + 1', 'forum_post_count%sql' => 'forum_post_count + ' . $topic->topic_post_count]));
        $this->topicsManager->update($topic_id, ArrayHash::from(['topic_forum_id' => $target_forum_id]));

        foreach ($posts as $post) {
            $post_ids[] = $post->post_id;
        }

        $this->postsManager->updateMulti($post_ids, ArrayHash::from(['post_forum_id' => $target_forum_id]));        
        $this->reportsManager->updateByTopic($topic_id, ArrayHash::from(['report_forum_id' => $target_forum_id]));
        $this->thanksManager->updateByTopic($topic_id, ArrayHash::from(['thank_forum_id' => $target_forum_id]));
    }

    /**
     * @param int $topic_from_id
     * @param int $topic_target_id
     * @param int $from_post_id
     *
     * @return Result|int
     */
    public function splitFrom($topic_from_id, $topic_target_id, $from_post_id)
    {
        $post_ids = [];
        $posts = $this->postsManager->getAllFluent()
            ->where('[post_topic_id] = %i', $topic_from_id)
            ->where('[post_id] > %i', $from_post_id)
            ->fetchAll();

        foreach ($posts as $post) {
            $post_ids[] = $post->post_id;
        }

        return $this->mergeWithPosts($topic_target_id, $post_ids);
    }

    /**
     * @param int $topic_from_id
     * @param     $topic_target_id
     * @param int $to_post_id
     *
     * @return Result|int
     */
    public function splitTo($topic_from_id, $topic_target_id, $to_post_id)
    {
        $post_ids = [];
        $posts = $this->postsManager->getAllFluent()
            ->where('[post_topic_id] = %i', $topic_from_id)
            ->where('[post_id] < %i', $to_post_id)
            ->fetchAll();

        foreach ($posts as $post) {
            $post_ids[] = $post->post_id;
        }

        return $this->mergeWithPosts($topic_target_id, $post_ids);
    }

    /**
     * @param int $topic_from_id
     * @param int $topic_target_id
     */
    public function mergeTwoTopics($topic_from_id, $topic_target_id)
    {
        if ($topic_from_id === $topic_target_id) {
            return false;
        }

        $topicFrom   = $this->topicsManager->getById($topic_from_id);
        $topicTarget = $this->topicsManager->getById($topic_target_id);

        if (!$topicFrom) {
            return false;
        }

        if (!$topicTarget) {
            return false;
        }

        $post_ids          = [];
        $thanksFromUsers   = [];
        $thanksTargetUsers = [];
        
        $posts  = $this->postsManager->getByTopic($topic_from_id);
        $thanks = $this->thanksManager->getAllByTopic($topic_from_id);

        // thanks begin
        $topicWatches = $this->topicWatchManager->getPairsByLeft($topic_from_id);
        $targetThanks = $this->thanksManager->getAllByTopic($topic_target_id);

        foreach ($thanks as $thanksFrom) {
            $thanksFromUsers[] = $thanksFrom->thank_user_id;
        }
              
        foreach ($targetThanks as $thanksTarget) {
            $thanksTargetUsers[] = $thanksTarget->thank_user_id;
        }
        
        $same_thanks     = array_intersect($thanksTargetUsers, $thanksFromUsers);
        $missing_thanks = array_diff($thanksFromUsers, $thanksTargetUsers);

        $this->usersManager->updateMulti(
            $same_thanks,
            ArrayHash::from(['user_thank_count%sql' => 'user_thank_count - 1'])
        );

        $this->thanksManager->deleteByUserAndTopic($same_thanks, $topic_from_id);
        // thanks end

        // topics watches begin
        $topicsWatchesFrom    = $this->topicWatchManager->getAllByLeft($topic_from_id);
        $topicsWatchesTarget  = $this->topicWatchManager->getAllByLeft($topic_target_id);

        $topic_watch_from_user_ids   = [];
        $topic_watch_target_user_ids = [];

        foreach ($topicsWatchesFrom as $topicsWatchFrom) {
            $topic_watch_from_user_ids[] = $topicsWatchFrom->user_id;
        }

        foreach ($topicsWatchesTarget as $topicWatchTarget) {
            $topic_watch_target_user_ids[] = $topicWatchTarget->user_id;
        }

        $same_watches    = array_intersect($topic_watch_from_user_ids, $topic_watch_target_user_ids);
        $missing_watches = array_diff($topic_watch_target_user_ids, $topic_watch_from_user_ids);

        $this->usersManager->updateMulti(
            $same_watches,
            ArrayHash::from(['user_watch_count%sql' => 'user_watch_count - 1'])
        );

        // topics watches

        $this->thanksManager->updateMultiByUser(
            $missing_thanks,
            ArrayHash::from(['thank_topic_id' => $topic_target_id])
        );
        $this->forumsManager->update(
            $topicFrom->topic_forum_id,
            ArrayHash::from(['forum_topic_count%sql' => 'forum_topic_count - 1'])
        );
        $this->thanksManager->deleteByTopic($topic_from_id);
        $this->reportsManager->updateByTopic(
            $topic_from_id,
            ArrayHash::from(['report_topic_id' => $topic_target_id, 'report_forum_id' => $topicTarget->topic_forum_id])
        );
        $this->topicWatchManager->mergeByLeft($topic_target_id, $topicWatches);
        $this->topicWatchManager->deleteByLeft($topic_from_id);
        
        foreach ($posts as $post) {
            $post_ids[] = $post->post_id;
        }

        $this->mergeWithPosts($topic_target_id, $post_ids);
        $this->topicsManager->delete($topic_from_id);
    }

    /**
     * @param int   $topic_target_id
     * @param array $post_ids
     *
     * @return Result|int
     */
    public function mergeWithPosts($topic_target_id, array $post_ids)
    {
        $this->postsManager->updateMulti($post_ids, ArrayHash::from(['post_topic_id' => $topic_target_id]));
        $last_post  = $this->postsManager->getLastByTopic($topic_target_id);
        $first_post = $this->postsManager->getFirstByTopic($topic_target_id);

        return $this->topicsManager->update($topic_target_id, ArrayHash::from([
            'topic_post_count%sql' => 'topic_post_count + ' . count($post_ids),
            'topic_first_post_id'  => $first_post->post_id,
            'topic_first_user_id'  => $first_post->post_user_id,
            'topic_last_post_id'   => $last_post->post_id,
            'topic_last_user_id'   => $last_post->post_user_id,
        ]));
    }
}
