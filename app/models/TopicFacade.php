<?php

namespace App\Models;

use App\Database\EntityManagerDecorator;
use App\Model\Entity\PollEntity;
use App\Model\Entity\PostEntity;
use App\Model\Entity\TopicWatchEntity;
use App\Model\Repository\ForumRepository;
use App\Model\Repository\PollRepository;
use App\Model\Repository\PostRepository;
use App\Model\Repository\ReportRepository;
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
    public function __construct(
        private readonly PostManager $postsManager,
        private readonly ThankManager $thanksManager,
        private readonly PostFacade $postFacade,
        private readonly TopicWatchFacade $topicWatchFacade,
        private readonly ThanksFacade $thanksFacade,
        private readonly ReportFacade $reportFacade,
        private readonly PollsFacade $pollsFacade,

        private readonly ThankRepository $thankRepository,
        private readonly TopicWatchRepository $topicWatchRepository,

        private readonly ForumRepository $forumRepository,
        private readonly TopicRepository $topicRepository,
        private readonly PostRepository $postRepository,

        private readonly PollRepository $pollRepository,

        private readonly ReportRepository $reportRepository,

        private readonly EntityManagerDecorator $em,
    ) {
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

        /*
        $this->usersManager->update($topicEntity->user->id, ArrayHash::from(
            [
                'user_topic_count%sql' => 'user_topic_count + 1',
                'user_watch_count%sql' => 'user_watch_count + 1'
            ]
        ));
        */

        /*
        $this->forumsManager->update(
            $topicEntity->forum->id,
            ArrayHash::from(['forum_topic_count%sql' => 'forum_topic_count + 1'])
        );
        */
    }

    public function delete(\App\Model\Entity\TopicEntity $topicEntity): void
    {
        $this->thanksFacade->deleteByTopic($topicEntity);
        $this->topicWatchFacade->deleteByTopic($topicEntity);
        $this->reportFacade->deleteByTopic($topicEntity);

        if ($topicEntity->poll !== null) {
            $this->pollsFacade->delete($topicEntity->poll);
        }

        /*
        $this->usersManager
            ->update($topicEntity->user->id, ArrayHash::from(['user_topic_count%sql' => 'user_topic_count - 1']));
        */

        $posts = $this->postRepository->getCountOfUsersByTopicId($topicEntity->id);

        /*
        $users = [];

        foreach ($posts as $post) {
            $users[] = $post->post_user_id;

            $this->usersManager->update(
                $post->post_user_id,
                ArrayHash::from(['user_post_count%sql' => 'user_post_count - ' . $post->post_count])
            );
        }
        */

        /*
        $this->forumsManager->update(
            $topicEntity->forum->id,
            ArrayHash::from(['forum_topic_count%sql' => 'forum_topic_count - 1'])
        );
        */

        $this->em->remove($topicEntity);
        $this->em->flush();
    }

    public function move($topic_id, $target_forum_id): void
    {
        $topic = $this->topicRepository->findOneBy(
            [
                'id' => $topic_id,
            ],
        );

        $targetForumEntity = $this->forumRepository
            ->findOneBy(
                [
                    'id' => $target_forum_id,
                ],
            );

        if ($topic === null) {
            return;
        }

        $sourceForumEntity = $topic->forum;

        $targetForumEntity = $this->forumRepository
            ->findOneBy(
                [
                    'id' => $target_forum_id,
                ]
            );

        if ($sourceForumEntity->id === $target_forum_id) {
            return;
        }

        $posts = $this->postRepository->findByTopicId($topic_id);
        $post_ids = Utils::arrayObjectColumn($posts, 'id');

        /*
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
        */

        foreach ($posts as $post) {
            $post->forum = $targetForumEntity;

            $this->em->persist($post);
        }

        $this->em->flush();

        $reports = $this->reportRepository
            ->findBy(
                [
                    'topic' => $topic,
                ],
            );

        foreach ($reports as $report) {
            $report->forum = $targetForumEntity;

            $this->em->persist($report);
        }

        $this->em->flush();

        $thanks = $this->thankRepository
            ->findBy(
                [
                    'topic' => $topic_id,
                ]
            );

        foreach ($thanks as $thank) {
            $thank->forum = $targetForumEntity;

            $this->em->persist($thank);
        }

        $this->em->flush();

        $topic->forum = $targetForumEntity;

        $this->em->persist($topic);
        $this->em->flush();
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

        $thanksFromUsers = Utils::arrayObjectColumn($thanks, 'thank_user_id');
        $thanksTargetUsers = Utils::arrayObjectColumn($targetThanks, 'thank_user_id');

        $same_thanks = array_intersect($thanksTargetUsers, $thanksFromUsers);
        $missing_thanks = array_diff($thanksFromUsers, $thanksTargetUsers);

        /*
        $this->usersManager->updateMulti(
            $same_thanks,
            ArrayHash::from(['user_thank_count%sql' => 'user_thank_count - 1'])
        );
        */

        $this->thanksManager->deleteByUsersAndTopic($same_thanks, $topic_from_id);
        // thanks end

        // topics watches begin
        $topicsWatchesFrom = $this->topicWatchRepository->findByTopic($topicFrom);
        $topicsWatchesTarget = $this->topicWatchRepository->findByTopic($topicTarget);

        $topic_watch_from_user_ids = Utils::arrayObjectColumn($topicsWatchesFrom, 'user_id');
        $topic_watch_target_user_ids = Utils::arrayObjectColumn($topicsWatchesTarget, 'user_id');

        $same_watches = array_intersect($topic_watch_from_user_ids, $topic_watch_target_user_ids);
        $missing_watches = array_diff($topic_watch_target_user_ids, $topic_watch_from_user_ids);

        /*
        $this->usersManager->updateMulti(
            $same_watches,
            ArrayHash::from(['user_watch_count%sql' => 'user_watch_count - 1'])
        );
        */

        // topics watches

        $thanks = $this->thankRepository
            ->findBy(
                [
                    'id' => $same_watches,
                ]
            );

        foreach ($thanks as $thank) {
            $thank->topic = $topicTarget;

            $this->em->persist($thank);
        }

        $this->em->flush();

        /*
        $this->thanksManager->updateMultiByUser(
            $missing_thanks,
            ArrayHash::from(['thank_topic_id' => $topic_target_id]),
        );
        $this->forumsManager->update(
            $topicFrom->topic_forum_id,
            ArrayHash::from(['forum_topic_count%sql' => 'forum_topic_count - 1'])
        );
        */

        $this->em->remove($topicFrom);
        $this->em->flush();

        $reportedTopics = $this->reportRepository->findByTopic($topicFrom);

        foreach ($reportedTopics as $reportedTopic) {
            $reportedTopic->topic = $topicTarget;
            $reportedTopic->forum = $topicTarget->forum;

            $this->em->persist($reportedTopic);
        }

        $this->em->flush();

        //$this->topicWatchManager->mergeByLeft($topic_target_id, $topicWatches);

        $missingWatches = $this->topicWatchRepository->findMissingWatchesByTopics($topicFrom, $topicTarget);

        foreach ($missingWatches as $missingWatch) {
            $newTopicWatch = new TopicWatchEntity();
            $newTopicWatch->topic = $topicTarget;
            $newTopicWatch->user = $missingWatch->user;

            $this->em->persist($newTopicWatch);
        }

        foreach ($topicFrom->watches as $watch) {
            $this->em->remove($watch);
        }

        $this->em->flush();

        foreach ($topicFrom->watches as $watch) {
            $this->em->remove($watch);
        }

        $this->em->flush();

        $post_ids = Utils::arrayObjectColumn($posts, 'post_id');

        $this->mergeWithPosts($topic_target_id, $post_ids);

        $this->em->remove($topicFrom);
        $this->em->flush();

        return true;
    }

    public function mergeWithPosts(int $topic_target_id, array $post_ids)
    {
        $this->postsManager->updateMulti($post_ids, ArrayHash::from(['post_topic_id' => $topic_target_id]));

        $topicEntity = $this->topicRepository
            ->findOneBy(
                [
                    'id' => $topic_target_id,
                ]
            );

        foreach ($post_ids as $postId) {
            $postEntity = $this->postRepository
                ->findOneBy(
                    [
                        'id' => $postId,
                    ]
                );

            $postEntity->topic = $topicEntity;

            $this->em->persist($postEntity);
        }

        $this->em->flush();;

        $lastPost = $this->postRepository->findLastByTopicId($topic_target_id);
        $firstPost = $this->postRepository->findFirstByTopicId($topic_target_id);

        /*
        return $this->topicsManager->update($topic_target_id, ArrayHash::from([
            //'topic_post_count%sql' => 'topic_post_count + ' . count($post_ids),
            'topic_first_post_id' => $firstPost->id,
            'topic_first_user_id' => $firstPost->user->id,

            'topic_last_post_id' => $lastPost->id,
            'topic_last_user_id' => $lastPost->user->id,
        ]));
        */
    }

    public function update(
        \App\Model\Entity\TopicEntity $topicEntity,
        PollEntity $pollEntity,
        PostEntity $postEntity
    ): void
    {
        $topicEntity->poll = $pollEntity;
        $postEntity->topic = $topicEntity;

        $this->em->persist($topicEntity);
        $this->em->persist($postEntity);
        $this->em->flush();

    }
}
