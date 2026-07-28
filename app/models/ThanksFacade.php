<?php

namespace App\Models;

use App\Database\EntityManagerDecorator;
use App\Model\Entity\ForumEntity;
use App\Model\Repository\ForumRepository;
use App\Model\Repository\TopicRepository;
use App\Models\Entity\PostEntity;
use App\Models\Entity\ThankEntity;
use App\Models\Entity\TopicEntity;
use App\Utils;
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
     * @var ThankManager $thanksManager
     */
    private ThankManager $thanksManager;
    
    /**
     * @var UsersManager $usersManager
     */
    private UsersManager $usersManager;

    /**
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
     * @var ForumManager $forumsManager
     */
    private ForumManager $forumsManager;

    /**
     * ThanksFacade constructor
     *
     * @param ThankManager $thanksManager
     * @param UsersManager  $usersManager
     * @param PostManager  $postsManager
     * @param TopicManager $topicsManager
     * @param ForumManager $forumsManager
     */
    public function __construct(
        ThankManager $thanksManager,
        UsersManager  $usersManager,
        PostManager  $postsManager,
        TopicManager $topicsManager,
        ForumManager $forumsManager,
        private readonly EntityManagerDecorator $em,
    ) {
        $this->thanksManager = $thanksManager;
        $this->usersManager  = $usersManager;
        $this->postsManager  = $postsManager;
        $this->topicsManager = $topicsManager;
        $this->forumsManager = $forumsManager;
    }

    public function add(\App\Model\Entity\ThankEntity $thank)
    {
        $this->usersManager->update(
            $thank->user->id,
            ArrayHash::from(['user_thank_count%sql' => 'user_thank_count + 1'])
        );

        $this->em->persist($thank);
        $this->em->flush();
    }
    
    /**
     *
     * @param int $category_id
     */
    public function deleteByCategory(int $category_id): void
    {
        /**
         * @var ForumRepository $forumRepository
         */
        $forumRepository = $this->em
            ->getRepository(ForumEntity::class);

        $forums = $forumRepository->findByCategoryId($category_id);
        
        foreach ($forums as $forum) {
            $this->deleteByForum($forum->forum_id);
        }
    }

    /**
     *
     * @param int $forum_id
     */
    public function deleteByForum(int $forum_id): void
    {
        /**
         * @var TopicRepository $topicRepository
         */
        $topicRepository = $this->em
            ->getRepository(\App\Model\Entity\TopicEntity::class);

       $topics = $topicRepository->findByForumId($forum_id);
        
        foreach ($topics as $topic) {
            $this->deleteByTopic($topic);
        }
    }

    /**
     *
     * @param TopicEntity $topic
     *
     * @return int
     */
    public function deleteByTopic(\App\Model\Entity\TopicEntity $topicEntity)
    {
        $thanks   = $this->thanksManager->getAllByTopic($topicEntity->id);
        $user_ids = Utils::arrayObjectColumn($thanks, 'thank_user_id');

        if (count($user_ids)) {
            $this->usersManager->updateMulti(
                $user_ids,
                ArrayHash::from(['user_thank_count%sql' => 'user_thank_count - 1'])
            );
        }

        return $this->thanksManager->deleteByTopic($topicEntity->id);
    }

    /**
     *
     * @param PostEntity $post
     *
     * @return bool
     */
    public function deleteByPost(\App\Model\Entity\PostEntity $post)
    {
        $count = $this->postsManager->getCountByUser($post->getPost_topic_id(), $post->getPost_user_id());

        if ($count === 1 || $count === 0) {
            $this->usersManager->update(
                $post->getPost_user_id(),
                ArrayHash::from(['user_thank_count%sql' => 'user_thank_count - 1'])
            );

            return $this->thanksManager->deleteByUsersAndTopic([$post->getPost_user_id()], $post->getPost_topic_id());
        } else {
            return false;
        }
    }
}
