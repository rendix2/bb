<?php

namespace App\Models;

use App\Database\EntityManagerDecorator;
use App\Model\Entity\PostEntity;
use App\Model\Entity\ThankEntity;
use App\Model\Entity\TopicEntity;
use App\Model\Repository\ForumRepository;
use App\Model\Repository\PostRepository;
use App\Model\Repository\ThankRepository;
use App\Model\Repository\TopicRepository;
use Nette\Utils\ArrayHash;

/**
 * Description of ThanksFacade
 *
 * @author rendix2
 * @package App\Models
 */
class ThanksFacade
{

    public function __construct(
        private readonly ThankManager           $thanksManager,
        private readonly UsersManager           $usersManager,
        private readonly EntityManagerDecorator $em,

        private readonly ThankRepository        $thankRepository,

        private readonly ForumRepository        $forumRepository,
        private readonly TopicRepository        $topicRepository,
        private readonly PostRepository         $postRepository,
    ) {
    }

    public function add(ThankEntity $thank)
    {
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

    public function deleteByTopic(TopicEntity $topicEntity): void
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

    public function deleteByPost(PostEntity $post)
    {
        $count = $this->postRepository->getCountByPost($post);

        if ($count === 1 || $count === 0) {
            return $this->thanksManager->deleteByUsersAndTopic([$post->user->id], $post->topic->id);
        } else {
            return false;
        }
    }
}
