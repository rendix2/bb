<?php declare(strict_types=1);

namespace App\Model\Repository;

use App\Model\Entity\ForumEntity;
use App\Model\Entity\PostEntity;
use App\Model\Entity\ReportEntity;
use App\Model\Entity\TopicEntity;
use Doctrine\ORM\EntityRepository;

/**
 * class ReportRepository
 *
 * @package App\Model\Repository
 * @extends EntityRepository<ReportEntity>
 */
class ReportRepository extends EntityRepository
{

    public function findByForum(ForumEntity $forumEntity): array
    {
        return $this->findBy(
            [
                'forum' => $forumEntity,
            ]
        );
    }

    public function findByForumId(int $forumId): array
    {
        return $this->findBy(
            [
                'forum' => $forumId,
            ]
        );
    }

    /**
     * @param TopicEntity $topicEntity
     *
     * @return ReportEntity[]
     */
    public function findByTopic(TopicEntity $topicEntity): array
    {
        return $this->findBy(
            [
                'topic' => $topicEntity,
            ]
        );
    }

    public function findByPost(PostEntity $postEntity): array
    {
        return $this->findBy(
            [
                'post' => $postEntity,
            ]
        );
    }

    /**
     * @param PostEntity[] $posts
     *
     * @return ReportEntity[]
     */
    public function findByPosts(array $posts): array
    {
        return $this->findBy(
            [
                'post' => $posts,
            ]
        );
    }
}