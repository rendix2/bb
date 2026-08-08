<?php

namespace App\Models;

use App\Database\EntityManagerDecorator;
use App\Model\Entity\ThankEntity;
use App\Model\Repository\ForumRepository;
use App\Model\Repository\PostRepository;
use App\Model\Repository\ThankRepository;
use App\Model\Repository\TopicRepository;
use App\Utils;
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

    public function __construct(
        ThankManager $thanksManager,
        UsersManager  $usersManager,
        private readonly EntityManagerDecorator $em,

        private readonly ThankRepository $thankRepository,

        private readonly ForumRepository $forumRepository,
        private readonly TopicRepository $topicRepository,
        private readonly PostRepository  $postRepository,
    ) {
        $this->thanksManager = $thanksManager;
        $this->usersManager  = $usersManager;
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
        $forums = $this->forumRepository->findByCategoryId($category_id);
        
        foreach ($forums as $forum) {
            $this->deleteByForum($forum->id);
        }
    }

    /**
     *
     * @param int $forum_id
     */
    public function deleteByForum(int $forum_id): void
    {
       $topics = $this->topicRepository->findByForumId($forum_id);
        
        foreach ($topics as $topic) {
            $this->deleteByTopic($topic);
        }
    }

    public function deleteByTopic(\App\Model\Entity\TopicEntity $topicEntity): void
    {
        /**
         * @var ThankEntity[] $thanks
         */
        $thanks = $this->thankRepository->findByTopicId($topicEntity->id);

        $users = [];

        foreach ($thanks as $thank) {
            $users[$thank->user->id] = $thank->user;
        }

        foreach ($users as $user) {
            $user->thank_count--;

            $this->em->persist($user);
        }

        $this->em->remove($topicEntity);

        $this->em->flush();
    }

    public function deleteByPost(\App\Model\Entity\PostEntity $post)
    {
        $count = $this->postRepository->getCountByPost($post);

        if ($count === 1 || $count === 0) {
            $this->usersManager->update(
                $post->user->id,
                ArrayHash::from(['user_thank_count%sql' => 'user_thank_count - 1'])
            );

            return $this->thanksManager->deleteByUsersAndTopic([$post->user->id], $post->topic->id);
        } else {
            return false;
        }
    }
}
