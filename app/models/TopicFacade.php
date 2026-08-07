<?php

namespace App\Models;

use App\Database\EntityManagerDecorator;
use App\Model\Entity\PostEntity;
use App\Model\Entity\TopicWatchEntity;
use App\Model\Repository\PollRepository;
use App\Model\Repository\PostRepository;
use App\Model\Repository\ThankRepository;
use App\Model\Repository\TopicRepository;
use App\Model\Repository\TopicWatchRepository;
use App\Utils;
use Dibi\Result;
use Nette\Utils\ArrayHash;

/**
 * Description of TopicFacade
 *
 * @author rendix2
 * @package App\Models
 */
class TopicFacade
{
    /**
     *
     * @var TopicManager $topicsManager
     */
    private TopicManager $topicsManager;

    /**
     *
     * @var TopicWatchManager $topicWatchManager
     */
    private TopicWatchManager $topicWatchManager;

    /**
     *
     * @var PostManager $postsManager
     */
    private PostManager $postsManager;

    /**
     *
     * @var UsersManager $usersManager
     */
    private UsersManager $usersManager;

    /**
     *
     * @var ThankManager $thanksManager
     */
    private ThankManager $thanksManager;

    /**
     *
     * @var ForumManager $forumsManager
     */
    private ForumManager $forumsManager;

    /**
     *
     * @var PostFacade $postFacade
     */
    private PostFacade $postFacade;

    /**
     * @var ReportManager $reportsManager
     */
    private ReportManager $reportsManager;
    
    /**
     *
     * @var TopicWatchFacade $topicWatchFacade
     */
    private TopicWatchFacade $topicWatchFacade;
    
    /**
     *
     * @var ThanksFacade $thanksFacade
     */
    private ThanksFacade $thanksFacade;

    /**
     * @var ReportFacade $reportFacade
     */
    private ReportFacade $reportFacade;
    
    /**
     *
     * @var PollsFacade $pollsFacade
     */
    private PollsFacade $pollsFacade;

    /**
     *
     * TopicFacade constructor
     *
     * @param TopicManager     $topicsManager
     * @param TopicWatchManager $topicWatchManager
     * @param PostManager      $postsManager
     * @param UsersManager      $usersManager
     * @param ThankManager     $thanksManager
     * @param ForumManager     $forumsManager
     * @param PostFacade        $postFacade
     * @param ReportManager    $reportsManager
     * @param TopicWatchFacade  $topicWatchFacade
     * @param ThanksFacade      $thanksFacade
     * @param ReportFacade      $reportFacade
     * @param PollsFacade       $pollsFacade
     */
    public function __construct(
        TopicManager      $topicsManager,
        TopicWatchManager $topicWatchManager,
        PostManager       $postsManager,
        UsersManager      $usersManager,
        ThankManager      $thanksManager,
        ForumManager      $forumsManager,
        PostFacade        $postFacade,
        ReportManager     $reportsManager,
        TopicWatchFacade  $topicWatchFacade,
        ThanksFacade      $thanksFacade,
        ReportFacade      $reportFacade,
        PollsFacade       $pollsFacade,

        private readonly ThankRepository      $thankRepository,
        private readonly TopicWatchRepository $topicWatchRepository,

        private readonly TopicRepository $topicRepository,
        private readonly PostRepository  $postRepository,

        private readonly PollRepository $pollRepository,

        private readonly EntityManagerDecorator $em,
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
        $this->pollsFacade       = $pollsFacade;
    }

    public function add(\App\Model\Entity\TopicEntity $topicEntity, PostEntity $postEntity): void
    {
        $this->em->persist($topicEntity);
        $this->em->flush();

        $topic_id = $topicEntity->id;
        $postEntity->topic = $topicEntity;
        
        if ($topicEntity->poll) {
            $this->pollsFacade->add($topicEntity->poll);
        }

        $topicWatchEntity = new TopicWatchEntity();
        $topicWatchEntity->topic = $topicEntity;
        $topicWatchEntity->user = $topicEntity->user;

        $this->em->persist($topicWatchEntity);
        $this->em->flush();

        $this->postFacade->add($postEntity);

        $topicEntity->firstPost = $postEntity;
        $topicEntity->lastPost = $postEntity;

        $this->usersManager->update($topicEntity->user->id, ArrayHash::from(
            [
                'user_topic_count%sql' => 'user_topic_count + 1',
                'user_watch_count%sql' => 'user_watch_count + 1'
            ]
        ));

        $this->forumsManager->update(
            $topicEntity->forum->id,
            ArrayHash::from(['forum_topic_count%sql' => 'forum_topic_count + 1'])
        );
    }

    public function delete(\App\Model\Entity\TopicEntity $topicEntity)
    {
        $this->thanksFacade->deleteByTopic($topicEntity);
        $this->topicWatchFacade->deleteByTopic($topicEntity);
        $this->reportFacade->deleteByTopic($topicEntity);
        
        if ($topicEntity->poll !== null) {
            $this->pollsFacade->delete($topicEntity->poll);
        }
        
        $this->usersManager
            ->update($topicEntity->user->id, ArrayHash::from(['user_topic_count%sql' => 'user_topic_count - 1']));
        
        $posts = $this->postRepository->getCountOfUsersByTopicId($topicEntity->id);
        $users = [];
        
        foreach ($posts as $post) {
            $users[] = $post->post_user_id;

            $this->usersManager->update(
                $post->post_user_id,
                ArrayHash::from(['user_post_count%sql' => 'user_post_count - ' . $post->post_count])
            );
        }

        $this->forumsManager->update(
            $topicEntity->forum->id,
            ArrayHash::from(['forum_topic_count%sql' => 'forum_topic_count - 1'])
        );

        return $this->topicsManager->delete($topicEntity->id);
    }

    /**
     * moves topic to another forum
     *
     * @param int $topic_id
     * @param int $target_forum_id
     *
     * @return bool|Result|int
     */
    public function move($topic_id, $target_forum_id)
    {
        $topic = $this->topicRepository->findOneBy(
            [
                'id' => $topic_id,
            ]
        );

        if ($topic === null) {
            return false;
        }
        
        $source_forum_id = $topic->topic_forum_id;

        if ($source_forum_id === $target_forum_id) {
            return false;
        }

        $posts    = $this->postRepository->findByTopicId($topic_id);
        $post_ids = Utils::arrayObjectColumn($posts, 'id');

        $this->forumsManager->update(
            $source_forum_id,
            ArrayHash::from(
                [
                    'forum_topic_count%sql' => 'forum_topic_count - 1',
                    'forum_post_count%sql'  => 'forum_post_count - ' . $topic->topic_post_count
                ]
            )
        );
        $this->forumsManager->update(
            $target_forum_id,
            ArrayHash::from(
                [
                    'forum_topic_count%sql' => 'forum_topic_count + 1',
                    'forum_post_count%sql'  => 'forum_post_count + ' . $topic->topic_post_count
                ]
            )
        );
        
        $this->postsManager->updateMulti($post_ids, ArrayHash::from(['post_forum_id' => $target_forum_id]));
        $this->reportsManager->updateByTopic($topic_id, ArrayHash::from(['report_forum_id' => $target_forum_id]));
        $this->thanksManager->updateByTopic($topic_id, ArrayHash::from(['thank_forum_id' => $target_forum_id]));
        
        return $this->topicsManager->update($topic_id, ArrayHash::from(['topic_forum_id' => $target_forum_id]));
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
        $posts = $this->postsManager->getAllFluent()
            ->where('[post_topic_id] = %i', $topic_from_id)
            ->where('[post_id] > %i', $from_post_id)
            ->fetchAll();
        
        $post_ids = Utils::arrayObjectColumn($posts, 'post_id');

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
        $posts = $this->postsManager->getAllFluent()
            ->where('[post_topic_id] = %i', $topic_from_id)
            ->where('[post_id] < %i', $to_post_id)
            ->fetchAll();

        $post_ids = Utils::arrayObjectColumn($posts, 'post_id');

        return $this->mergeWithPosts($topic_target_id, $post_ids);
    }

    /**
     * @param int $topic_from_id
     * @param int $topic_target_id
     *
     * @return bool
     */
    public function mergeTwoTopics(int $topic_from_id, int $topic_target_id): bool
    {
        if ($topic_from_id === $topic_target_id) {
            return false;
        }

        $topicFrom = $this->topicRepository
            ->findOneBy(
                [
                    'id' => $topic_from_id,
                ],
            );

        $topicTarget = $this->topicRepository
            ->findOneBy(
                [
                    'id' => $topic_target_id,
                ],
            );

        if ($topicFrom === null) {
            return false;
        }

        if ($topicTarget === null) {
            return false;
        }

        $posts = $this->postRepository->findByTopicId($topic_from_id);

        $thanks = $this->thankRepository->findByTopicId($topic_from_id);


        // thanks begin
        $topicWatches = $this->topicWatchRepository->findByTopic($topicFrom);
        $targetThanks = $this->thankRepository->findByTopicId($topic_target_id);

        $thanksFromUsers   = Utils::arrayObjectColumn($thanks, 'thank_user_id');
        $thanksTargetUsers = Utils::arrayObjectColumn($targetThanks, 'thank_user_id');
        
        $same_thanks     = array_intersect($thanksTargetUsers, $thanksFromUsers);
        $missing_thanks = array_diff($thanksFromUsers, $thanksTargetUsers);

        $this->usersManager->updateMulti(
            $same_thanks,
            ArrayHash::from(['user_thank_count%sql' => 'user_thank_count - 1'])
        );

        $this->thanksManager->deleteByUsersAndTopic($same_thanks, $topic_from_id);
        // thanks end

        // topics watches begin
        $topicsWatchesFrom    = $this->topicWatchRepository->findByTopic($topicFrom);
        $topicsWatchesTarget  = $this->topicWatchRepository->findByTopic($topicTarget);

        $topic_watch_from_user_ids   = Utils::arrayObjectColumn($topicsWatchesFrom, 'user_id');
        $topic_watch_target_user_ids = Utils::arrayObjectColumn($topicsWatchesTarget, 'user_id');

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

        $this->em->remove($topicFrom);
        $this->em->flush();

        $this->reportsManager->updateByTopic(
            $topic_from_id,
            ArrayHash::from(['report_topic_id' => $topic_target_id, 'report_forum_id' => $topicTarget->topic_forum_id])
        );
        $this->topicWatchManager->mergeByLeft($topic_target_id, $topicWatches);
        $this->topicWatchManager->deleteByLeft($topic_from_id);
        
        $post_ids = Utils::arrayObjectColumn($posts, 'post_id');

        $this->mergeWithPosts($topic_target_id, $post_ids);
        $this->topicsManager->delete($topic_from_id);

        return true;
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
        $last_post = $this->postRepository->findLastByTopicId($topic_target_id);
        $first_post = $this->postRepository->findFirstByTopicId($topic_target_id);

        return $this->topicsManager->update($topic_target_id, ArrayHash::from([
            'topic_post_count%sql' => 'topic_post_count + ' . count($post_ids),
            'topic_first_post_id'  => $first_post->id,
            'topic_first_user_id'  => $first_post->user->id,

            'topic_last_post_id'   => $last_post->id,
            'topic_last_user_id'   => $last_post->user->id,
        ]));
    }

    public function update(\App\Model\Entity\TopicEntity $topicEntity)
    {
        $res = $this->topicsManager->update(
            $topicEntity->id,
            ArrayHash::from(['topic_name' => $topicEntity->getTopic_name()])
        );

        $this->postsManager->update(
            $topicEntity->getPost()->getPost_id(),
            ArrayHash::from(['post_text' => $topicEntity->getPost()->getPost_text()])
        );

        $topicHasPoll = $this->pollRepository->findByTopic($topicEntity);

        if ($topicHasPoll) {
            $poll = $topicEntity->getPoll();
            $poll->setPoll_topic_id($topicEntity->getTopic_id());
            
            if ($poll->poll_question) {
                $this->pollsFacade->update($poll);
            } else {
                $this->pollsFacade->delete($poll);
            }
        } else {
            if ($topicEntity->getPoll()) {
                $this->pollsFacade->add($topicEntity->getPoll());
            }
        }

        return $res;
    }
}
