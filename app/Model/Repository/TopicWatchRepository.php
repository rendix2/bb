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

}
