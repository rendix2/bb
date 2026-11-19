<?php declare(strict_types=1);

namespace App\Model\Service;

use App\Database\EntityManagerDecorator;
use App\Model\Entity\PostEntity;
use App\Settings\TopicsSetting;

/**
 * On which page of its topic a post is (posts oldest first, topics.pagination.itemsPerPage per page).
 */
class TopicPaginationService
{
    public function __construct(
        private readonly EntityManagerDecorator $em,
        private readonly TopicsSetting $topicsSetting,
    ) {
    }

    public function postsPerPage() : int
    {
        return max(1, (int) $this->topicsSetting->get()['pagination']['itemsPerPage']);
    }

    public function pageOf(PostEntity $post) : int
    {
        $before = (int) $this->em->getRepository(PostEntity::class)
            ->createQueryBuilder('_p')
            ->select('COUNT(_p.id)')
            ->where('_p.topic = :topic')
            ->andWhere('_p.id < :id')
            ->setParameter('topic', $post->topic)
            ->setParameter('id', $post->id)
            ->getQuery()
            ->getSingleScalarResult();

        return intdiv($before, $this->postsPerPage()) + 1;
    }
}
