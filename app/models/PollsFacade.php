<?php

namespace App\Models;

use App\Database\EntityManagerDecorator;
use App\Model\Repository\PollAnswerRepository;
use App\Model\Repository\PollRepository;
use App\Model\Repository\PollVoteRepository;
use App\Models\Entity\PollEntity;

/**
 * Description of PollsFacade
 *
 * @author rendix2
 * @package App\Models
 */
class PollsFacade
{
    public function __construct(
        private readonly EntityManagerDecorator $em,
    ) {
    }

    public function add(\App\Model\Entity\PollEntity $poll): void
    {
        /*
        $poll_id = $this->pollsManager->add($poll->getArrayHash());
        
        $poll->setPoll_id($poll_id);
        
        foreach ($poll->answers as $answer) {
            $answer->setPoll_id($poll_id);
            $this->pollsAnswersManager->add($answer->getArrayHash());
        }
        */

        $this->em->persist($poll);
        $this->em->flush();
    }
    
    public function update(\App\Model\Entity\PollEntity $pollEntity): void
    {
        /*
        $this->pollsManager->update($pollEntity->id, $pollEntity->getArrayHash());
        
        foreach ($pollEntity->answers as $answer) {
            $answer_exists = $this->pollAnswerRepository
                ->findOneBy(
                    [
                        'id' => $answer->id,
                    ]
                );
            
            if ($answer_exists) {
                $this->pollsAnswersManager->update($answer->id, $answer->getArrayHash());
            } else {
                $this->pollsAnswersManager->add($answer->getArrayHash());
            }
        }
        */

        $this->em->persist($pollEntity);
        $this->em->flush();
    }

    public function delete(\App\Model\Entity\PollEntity $poll): void
    {
        /*
        $this->pollsManager->delete($poll->id);
        $this->pollsAnswersManager->deleteByPoll($poll->id);
        $this->pollsVotesManager->deleteByPoll($poll->id);
        */

        $this->em->remove($poll);
        $this->em->flush();
    }
}
