<?php declare(strict_types=1);

namespace App\Model\Repository;

use App\Model\Entity\ThankEntity;
use App\Model\Entity\TopicEntity;
use Doctrine\ORM\EntityRepository;

/**
 * class TopicRepository
 *
 * @package App\Model\Repository
 * @extends EntityRepository<ThankEntity>
 */
class ThankRepository extends EntityRepository
{

    public function findByTopicId(int $topicId) : array
    {
        return $this->findBy(
            [
                'topic' => $topicId,
            ]
        );
    }

    public function findByTopic(TopicEntity $topicEntity) : array
    {
        return $this->findBy(
            [
                'topic' => $topicEntity,
            ]
        );
    }

    public function findByTopicJoinedUser(int $topicId): array
    {
        return $this
            ->createQueryBuilder('_t')

            ->addSelect('_u')
            ->join('_t.user', '_u')

            ->where('_t.topic = :topic')
            ->setParameter('topic', $topicId)

            ->getQuery()
            ->getResult();
    }

    public function findByTopicJoinedTopic(int $topicId): array
    {
        return $this
            ->createQueryBuilder('_thank')

            ->addSelect('_topic')
            ->join('_thank.topic', '_topic')

            ->where('_thank.topic = :topic')
            ->setParameter('topic', $topicId)

            ->getQuery()
            ->getResult();
    }


}