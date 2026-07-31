<?php

namespace App\Models;

use App\Database\EntityManagerDecorator;
use App\Model\Entity\ForumEntity;
use App\Model\Repository\ForumRepository;
use App\Model\Repository\PostRepository;
use App\Model\Repository\TopicRepository;
use App\Models\Entity\PostEntity;
use App\Models\Entity\TopicEntity;
use App\Utils;
use Dibi\Result;
use Nette\Utils\ArrayHash;

/**
 * Description of TopicWatchFacade
 *
 * @author rendix2
 * @package App\Models
 */
class TopicWatchFacade
{
    /**
     *
     * @var UsersManager $usersManager
     */
    private UsersManager $usersManager;
    
    /**
     * @var TopicWatchManager $topicWatchManager
     */
    private TopicWatchManager $topicWatchManager;
    
    /**
     *
     * @var PostManager $postsManager
     */
    private PostManager $postsManager;


    public function __construct(
        UsersManager      $usersManager,
        TopicWatchManager $topicWatchManager,
        PostManager      $postsManager,
        private readonly EntityManagerDecorator $em,

        private readonly ForumRepository $forumRepository,
        private readonly TopicRepository $topicRepository,
        private readonly PostRepository  $postRepository,
    ) {
        $this->usersManager      = $usersManager;
        $this->topicWatchManager = $topicWatchManager;
        $this->postsManager      = $postsManager;
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

    /**
     *
     * @param \App\Model\Entity\TopicEntity $topicEntity
     *
     * @return Result|int
     */
    public function deleteByTopic(\App\Model\Entity\TopicEntity $topicEntity): int
    {
        $topicsWatches = $this->topicWatchManager->getAllByLeft($topicEntity->id);
        $user_ids      = Utils::arrayObjectColumn($topicsWatches, 'user_id');

        if (count($user_ids)) {
            $this->usersManager->updateMulti(
                $user_ids,
                ArrayHash::from(['user_watch_count%sql' => 'user_watch_count - 1'])
            );
        }
        
        return $this->topicWatchManager->deleteByLeft($topicEntity->id);
    }

    /**
     *
     * @param PostEntity $post
     */
    public function deleteByPost(\App\Model\Entity\PostEntity $post): void
    {
        $postCount = $this->postRepository->getCountOfUsersByTopicId($post->topic->id);

        foreach ($postCount as $ps) {
            // check if user has there only one post so we can delete his topic watching
            // else he can still want to watch this topic
            if ($ps->post_count === 1 || $ps->post_count === 0) {
                $check = $this->topicWatchManager->fullCheck($post->topic->id, $ps->post_user_id);

                if ($check) {
                    $this->topicWatchManager->delete($post->topic->id, $ps->post_user_id);
                    $this->usersManager->update(
                        $post->user->id,
                        ArrayHash::from(['user_watch_count%sql' => 'user_watch_count - 1'])
                    );
                }
            }
        }
    }
}
