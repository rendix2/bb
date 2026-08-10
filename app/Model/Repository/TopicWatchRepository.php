<?php declare(strict_types=1);

namespace App\Model\Repository;

use App\Model\Entity\TopicEntity;
use App\Model\Entity\TopicWatchEntity;
use App\Model\Entity\UserEntity;
use Doctrine\ORM\EntityRepository;

/**
 * class UserRepository
 *
 * @package App\Model\Repository
 * @extends EntityRepository<TopicWatchEntity>
 */
class TopicWatchRepository extends EntityRepository
{
    public function findByUserId(int $userId): array
    {
        return $this->findBy(
            [
                'user' => $userId,
            ]
        );
    }

    public function findByUser(UserEntity $userEntity): array
    {
        return $this->findBy(
            [
                'user' => $userEntity,
            ]
        );
    }

    public function findByTopic(TopicEntity $topicEntity): array
    {
        return $this->findBy(
            [
                'topic' => $topicEntity,
            ]
        );
    }

    /**
     *
     * @param TopicEntity $topicEntity
     * @return array<int|string>
     */
    public function findUserIdsByTopic(TopicEntity $topicEntity): array
    {
        return $this->createQueryBuilder('_tw')
            ->select('_u.id')
            ->innerJoin('_tw.user', '_u')

            ->where('_tw.topic = :topic')
            ->setParameter('topic', $topicEntity)

            ->getQuery()
            ->getSingleColumnResult();
    }

    /**
     *
     * @param TopicEntity $topicFrom
     * @param TopicEntity $topicTarget
     * @return TopicWatchEntity[]
     */
    public function findMissingWatchesByTopics(TopicEntity $topicFrom, TopicEntity $topicTarget): array
    {
        $targetUserIds = $this->findUserIdsByTopic($topicTarget);

        $qb = $this->createQueryBuilder('_tw')
            ->where('_tw.topic = :topicFrom')
            ->setParameter('topicFrom', $topicFrom);

        if ($targetUserIds !== []) {
            $qb->andWhere('_tw.user NOT IN (:targetUserIds)')
                ->setParameter('targetUserIds', $targetUserIds);
        }

        return $qb->getQuery()->getResult();
    }

}
