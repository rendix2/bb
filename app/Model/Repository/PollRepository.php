<?php

namespace App\Model\Repository;

use App\Model\Entity\PollEntity;
use App\Model\Entity\TopicEntity;
use Doctrine\ORM\EntityRepository;

/**
 * class PostRepository
 *
 * @package App\Model\Repository
 * @extends EntityRepository<PollEntity>
 */
class PollRepository extends EntityRepository
{

    public function getByTopicId($topic_id): ?PollEntity
    {
        return $this->findOneBy(
            [
                'topic' => $topic_id,
            ]
        );
    }

    public function getByTopic(TopicEntity $topicEntity): ?PollEntity
    {
        return $this->findOneBy(
            [
                'topic' => $topicEntity,
            ]
        );
    }

}