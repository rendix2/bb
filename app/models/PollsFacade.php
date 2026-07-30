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
    /**
     *
     * @var PollsManager $pollsManager
     */
    private PollsManager $pollsManager;
    
    /**
     *
     * @var PollsAnswersManager $pollsAnswersManager
     */
    private PollsAnswersManager $pollsAnswersManager;
    
    /**
     *
     * @var PollsVotesManager $pollsVotesManager
     */
    private PollsVotesManager $pollsVotesManager;

    /**
     * PollsFacade constructor.
     *
     * @param PollsManager        $pollsManager
     * @param PollsAnswersManager $pollsAnswersManager
     * @param PollsVotesManager   $pollsVotesManager
     */
    public function __construct(
        PollsManager        $pollsManager,
        PollsAnswersManager $pollsAnswersManager,
        PollsVotesManager   $pollsVotesManager,
        private readonly EntityManagerDecorator $em,

        private readonly PollRepository       $pollRepository,
        private readonly PollAnswerRepository $pollAnswerRepository,
        private readonly PollVoteRepository   $pollVoteRepository,

    ) {
        $this->pollsManager        = $pollsManager;
        $this->pollsAnswersManager = $pollsAnswersManager;
        $this->pollsVotesManager   = $pollsVotesManager;
    }

    public function add(\App\Model\Entity\PollEntity $poll)
    {
        $poll_id = $this->pollsManager->add($poll->getArrayHash());
        
        $poll->setPoll_id($poll_id);
        
        foreach ($poll->answers as $answer) {
            $answer->setPoll_id($poll_id);
            $this->pollsAnswersManager->add($answer->getArrayHash());
        }
    }
    
    public function update(\App\Model\Entity\PollEntity $poll)
    {
        $this->pollsManager->update($poll->id, $poll->getArrayHash());
        
        foreach ($poll->answers as $answer) {
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
    }

    public function delete(\App\Model\Entity\PollEntity $poll)
    {
        $this->pollsManager->delete($poll->id);
        $this->pollsAnswersManager->deleteByPoll($poll->id);
        $this->pollsVotesManager->deleteByPoll($poll->id);
    }
}
