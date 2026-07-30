<?php

namespace App\Model\Repository;

use App\Model\Entity\PollAnswerEntity;
use Doctrine\ORM\EntityRepository;

/**
 * class PollAnswerRepository
 *
 * @package App\Model\Repository
 * @extends EntityRepository<PollAnswerEntity>
 */
class PollAnswerRepository extends EntityRepository
{

    public function findByPollId(int $pollId): array
    {
        return $this->findBy(
            [
                'poll' => $pollId,
            ]
        );
    }

}