<?php

namespace App\Models;

use App\Database\EntityManagerDecorator;
use App\Model\Entity\PostHistoryEntity;
use App\Model\Entity\TopicWatchEntity;
use App\Model\Repository\CategoryRepository;
use App\Model\Repository\ForumRepository;
use App\Model\Repository\PostRepository;
use App\Model\Repository\TopicRepository;
use App\Model\Repository\TopicWatchRepository;
use App\Model\Repository\UserRepository;
use App\Settings\TopicsSetting;
use Dibi\Result;
use Nette\Utils\ArrayHash;

/**
 * Description of PostFacade
 *
 * @author rendix2
 * @package App\Models
 */
class PostFacade
{
    /**
     *
     * @var PostManager $postsManager
     */
    private PostManager $postsManager;

    /**
     *
     * @var TopicManager $topicsManager
     */
    private TopicManager $topicsManager;


    /**
     *
     * @var UsersManager $usersManager
     */
    private UsersManager $usersManager;

    /**
     *
     * @var ReportManager $reportsManager
     */
    private ReportManager $reportsManager;

    /**
     *
     * @var ForumManager $forumsManager
     */
    private ForumManager $forumsManager;

    /**
     *
     * @var ThanksFacade $thanksFacade
     */
    private ThanksFacade $thanksFacade;

    /**
     *
     * @var TopicWatchFacade $topicWatchFacade
     */
    private TopicWatchFacade $topicWatchFacade;

    /**
     *
     * @var PollsFacade $pollsFacade
     */
    private PollsFacade $pollsFacade;

    /**
     *
     * @var TopicsSetting $topicSettings
     */
    private TopicsSetting $topicSettings;

    /**
     *
     * @var FilesManager $filesManager
     */
    private FilesManager $filesManager;

    /**
     * @var Posts2FilesManager $posts2FilesManger
     */
    private Posts2FilesManager $posts2FilesManger;

    public function __construct(
        PostManager                             $postsManager,
        TopicManager                            $topicsManager,
        UsersManager                            $usersManager,
        ReportManager                           $reportsManager,
        ForumManager                            $forumsManager,
        ThanksFacade                            $thanksFacade,
        TopicWatchFacade                        $topicWatchFacade,
        PollsFacade                             $pollsFacade,
        TopicsSetting                           $topicSettings,
        FilesManager                            $filesManager,
        Posts2FilesManager                      $posts2FilesManger,
        private readonly EntityManagerDecorator $em,

        private readonly CategoryRepository     $categoryRepository,
        private readonly ForumRepository        $forumRepository,
        private readonly TopicRepository        $topicRepository,
        private readonly PostRepository         $postRepository,

        private readonly UserRepository         $userRepository,
        private readonly TopicWatchRepository   $topicWatchRepository,
    )
    {
        $this->postsManager = $postsManager;
        $this->topicsManager = $topicsManager;
        $this->usersManager = $usersManager;
        $this->reportsManager = $reportsManager;
        $this->forumsManager = $forumsManager;
        $this->thanksFacade = $thanksFacade;
        $this->topicWatchFacade = $topicWatchFacade;
        $this->pollsFacade = $pollsFacade;
        $this->topicSettings = $topicSettings;
        $this->filesManager = $filesManager;
        $this->posts2FilesManger = $posts2FilesManger;
    }

    public function add(\App\Model\Entity\PostEntity $post)
    {
        $this->em->persist($post);
        $this->em->flush();

        $post_id = $post->id;

        $user_id = $post->user->id;
        $forum_id = $post->forum->id;
        $topic_id = $post->topic->id;

        $topicEntity = $this->topicRepository->findOneBy(
            [
                'id' => $topic_id,
            ]
        );

        $files = $post->files;
        $files_ids = [];

        if ($files) {
            foreach ($files as $file) {
                $file_id = $this->filesManager->add($file->getArrayHash());

                $file->setFile_id($file_id);
                $files_ids[] = $file->getFile_id();
            }

            $this->posts2FilesManger->addByLeft($post_id, $files_ids);
        }

        $this->topicsManager->update(
            $topic_id,
            ArrayHash::from([
                'topic_post_count%sql' => 'topic_post_count + 1',
                'topic_last_user_id' => $user_id,
                'topic_last_post_id' => $post_id,
                'topic_page_count' => ceil(($topicEntity->getTopic_post_count() + 1) / $this->topicSettings->get()['pagination']['itemsPerPage'])
            ])
        );

        $topicEntity = $this->topicRepository
            ->findOneBy(
                [
                    'id' => $topic_id,
                ]
            );

        $userEntity = $this->userRepository
            ->findOneBy(
                [
                    'id' => $user_id,
                ]
            );

        $topicWatchEntity = $this->topicWatchRepository
            ->findOneBy(
                [
                    'topic' => $topicEntity,
                    'user' => $userEntity,
                ]
            );

        $watch = [];

        if ($topicWatchEntity === null) {
            $topicWatchEntity = new TopicWatchEntity();
            $topicWatchEntity->topic = $topicEntity;
            $topicWatchEntity->user = $userEntity;

            $this->em->persist($topicWatchEntity);
            $this->em->flush();

            $watch = ['user_watch_count%sql' => 'user_watch_count + 1'];
        }

        $postHistoryEntity = new PostHistoryEntity();
        $postHistoryEntity->post = $post;
        $postHistoryEntity->user = $post->user;
        $postHistoryEntity->title = $post->title;
        $postHistoryEntity->text = $post->text;

        $this->em->persist($postHistoryEntity);
        $this->em->flush();

        $this->usersManager->update($user_id, ArrayHash::from([
                'user_post_count%sql' => 'user_post_count + 1',
                'user_last_post_time' => time()
            ] + $watch));

        $this->forumsManager->update($forum_id, ArrayHash::from(['forum_post_count%sql' => 'forum_post_count + 1']));

        return $post_id;
    }

    public function update(\App\Model\Entity\PostEntity $postEntity)
    {
        $postHistoryEntity = new PostHistoryEntity();
        $postHistoryEntity->post = $postEntity;
        $postHistoryEntity->title = $postEntity->title;
        $postHistoryEntity->text = $postEntity->text;
        $postHistoryEntity->user = $postEntity->user;

        $this->em->persist($postHistoryEntity);
        $this->em->persist($postEntity);

        $this->em->flush();
    }

    /**
     * @param \App\Model\Entity\TopicEntity $topicEntity
     * @param \App\Model\Entity\PostEntity $postEntity
     * @return Result|int
     */
    public function delete(\App\Model\Entity\TopicEntity $topicEntity, \App\Model\Entity\PostEntity $postEntity)
    {
        $this->usersManager->update(
            $postEntity->user->id,
            ArrayHash::from(['user_post_count%sql' => 'user_post_count - 1'])
        );
        $this->topicsManager->update(
            $postEntity->topic->id,
            ArrayHash::from([
                'topic_post_count%sql' => 'topic_post_count - 1',
                'topic_page_count' => ceil(($topicEntity->getTopic_post_count() - 1) / $this->topicSettings->get()['pagination']['itemsPerPage'])
            ])
        );

        $this->thanksFacade->deleteByPost($postEntity);

        foreach ($postEntity->historyPosts as $historyPost)
        {
            $this->em->remove($historyPost);
        }

        $this->em->flush();

        $this->topicWatchFacade->deleteByPost($postEntity);
        $this->reportsManager->deleteByPost($postEntity->id);
        $this->forumsManager->update(
            $postEntity->forum->id,
            ArrayHash::from(['forum_post_count%sql' => 'forum_post_count - 1'])
        );

        // recount last post info
        $this->em->remove($postEntity);
        $this->em->flush();

        // last post
        if ($topicEntity->lastPost->id === $postEntity->id && $topicEntity->firstPost->id !== $postEntity->id) {
            $last_post = $this->postRepository->findLastByTopicId($postEntity->topic->id);

            if ($last_post) {
                $this->topicsManager->update($postEntity->topic->id, ArrayHash::from([
                    'topic_last_post_id' => $last_post->id,
                    'topic_last_user_id' => $last_post->user->id
                ]));
            }
        } elseif ($topicEntity->firstPost->id === $postEntity->id && $topicEntity->lastPost->id !== $postEntity->id) {
            $first_post = $this->postRepository->findFirstByTopicId($postEntity->topic->id);

            if ($first_post) {
                $this->topicsManager->update($postEntity->topic->id, ArrayHash::from([
                    'topic_first_post_id' => $first_post->id,
                    'topic_first_user_id' => $first_post->user->id
                ]));
            }
        } elseif ($topicEntity->lastPost->id === $topicEntity->firstPost->id && $topicEntity->firstPost->id === $postEntity->id) {
            $this->forumsManager->update(
                $postEntity->forum->id,
                ArrayHash::from(['forum_topic_count%sql' => 'forum_topic_count - 1'])
            );
            $this->thanksFacade->deleteByTopic($topicEntity);
            $this->reportsManager->deleteByTopic($topicEntity->id);
            $this->topicWatchFacade->deleteByTopic($topicEntity);
            $this->usersManager->update(
                $topicEntity->user->id,
                ArrayHash::from(['user_topic_count%sql' => 'user_topic_count - 1'])
            );

            if ($topicEntity->poll) {
                $this->pollsFacade->delete($topicEntity->poll);
            }

            $this->topicsManager->delete($topicEntity->id);

            return 2;
        }

        $lastPostOfUser = $this->postRepository->findLastByUserId($postEntity->user->id);

        if ($lastPostOfUser) {
            $this->usersManager->update(
                $postEntity->user->id,
                ArrayHash::from(['user_last_post_time' => $lastPostOfUser->createdAt])
            );
        } else {
            $this->usersManager->update(
                $postEntity->user->id,
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
    public function move(int $post_id, int $target_topic_id): bool
    {
        $postEntity = $this->postRepository->findOneBy(
            [
                'id' => $post_id,
            ]
        );

        if ($postEntity === null) {
            return false;
        }

        $targetTopicEntity = $this->topicRepository->findOneBy(
            [
                'id' => $target_topic_id,
            ]
        );

        if (!$targetTopicEntity) {
            return false;
        }

        $source_topic_id = $postEntity->topic->id;
        $source_forum_id = $postEntity->forum->id;

        $target_forum_id = $targetTopicEntity->topic_forum_id;

        if ($source_topic_id !== $target_topic_id) {
            $this->topicsManager->update(
                $source_topic_id,
                ArrayHash::from(['topic_post_count%sql' => 'topic_post_count - 1'])
            );
            $this->topicsManager->update(
                $target_topic_id,
                ArrayHash::from(['topic_post_count%sql' => 'topic_post_count + 1'])
            );
        }

        if ($source_forum_id !== $target_forum_id) {
            $this->forumsManager->update(
                $source_forum_id,
                ArrayHash::from(['forum_post_count%sql' => 'forum_post_count - 1'])
            );
            $this->forumsManager->update(
                $target_forum_id,
                ArrayHash::from(['forum_post_count%sql' => 'forum_post_count + 1'])
            );
        }

        $this->reportsManager->updateByPost($post_id, ArrayHash::from(['report_topic_id' => $target_topic_id]));

        return $this->postsManager->update($post_id, ArrayHash::from(['post_topic_id' => $target_topic_id]));
    }
}
