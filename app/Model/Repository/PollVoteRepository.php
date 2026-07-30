<?php

namespace App\Model\Repository;

use App\Model\Entity\PollVoteEntity;
use Doctrine\ORM\EntityRepository;

/**
 * class PollAnswerRepository
 *
 * @package App\Model\Repository
 * @extends EntityRepository<PollVoteEntity>
 */
class PollVoteRepository extends EntityRepository
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