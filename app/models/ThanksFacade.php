<?php

namespace App\Models;

use App\Database\EntityManagerDecorator;
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

    /**
     * @var PostManager $postsManager
     */
    private PostManager $postsManager;

    public function __construct(
        ThankManager $thanksManager,
        UsersManager  $usersManager,
        PostManager  $postsManager,
        private readonly EntityManagerDecorator $em,

        private readonly ThankRepository $thankRepository,

        private readonly ForumRepository $forumRepository,
        private readonly TopicRepository $topicRepository,
        private readonly PostRepository  $postRepository,
    ) {
        $this->thanksManager = $thanksManager;
        $this->usersManager  = $usersManager;
        $this->postsManager  = $postsManager;
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

    public function deleteByTopic(\App\Model\Entity\TopicEntity $topicEntity)
    {
        $thanks = $this->thankRepository->findByTopicId($topicEntity->id);

        $user_ids = Utils::arrayObjectColumn($thanks, 'thank_user_id');

        if (count($user_ids)) {
            $this->usersManager->updateMulti(
                $user_ids,
                ArrayHash::from(['user_thank_count%sql' => 'user_thank_count - 1'])
            );
        }

        return $this->thanksManager->deleteByTopic($topicEntity->id);
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
