<?php

namespace App\Models;

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
    private $pollsManager;
    
    /**
     *
     * @var PollsAnswersManager $pollsAnswersManager
     */
    private $pollsAnswersManager;
    
    /**
     *
     * @var PollsVotesManager $pollsVotesManager
     */
    private $pollsVotesManager;
    
    /**
     *
     * @param PollsManager        $pollsManager
     * @param PollsAnswersManager $pollsAnswersManager
     * @param PollsVotesManager   $pollsVotesManager
     */
    public function __construct(
        PollsManager        $pollsManager,
        PollsAnswersManager $pollsAnswersManager,
        PollsVotesManager   $pollsVotesManager
    ) {
        $this->pollsManager        = $pollsManager;
        $this->pollsAnswersManager = $pollsAnswersManager;
        $this->pollsVotesManager   = $pollsVotesManager;
    }
    
    public function __destruct()
    {
        $this->pollsManager        = null;
        $this->pollsAnswersManager = null;
        $this->pollsVotesManager   = null;
    }
    
    /**
     *
     * @return PollsManager
     */
    public function getPollsManager()
    {
        return $this->pollsManager;
    }
    
    /**
     *
     * @return PollsAnswersManager
     */
    public function getPollsAnswersManager()
    {
        return $this->pollsAnswersManager;
    }
    
    /**
     *
     * @return PollsVotesManager
     */
    public function getPollsVotesManager()
    {
        return $this->pollsVotesManager;
    }

    /**
     *
     * @param PollEntity $poll
     */
    public function add(PollEntity $poll)
    {
        $poll_id = $this->pollsManager->add($poll->getArrayHash());
        
        $poll->setPoll_id($poll_id);
        
        foreach ($poll->getPollAnswers() as $answer) {
            $answer->setPoll_id($poll_id);
            $this->pollsAnswersManager->add($answer->getArrayHash());
        }
    }
    
    /**
     *
     * @param PollEntity $poll
     */
    public function update(PollEntity $poll)
    {
        $this->pollsManager->update($poll->getPoll_id(), $poll->getArrayHash());
        
        foreach ($poll->getPollAnswers() as $answer) {
            $answer_exists = $this->pollsAnswersManager->getById($answer->getPoll_answer_id());
            
            if ($answer_exists) {
                $this->pollsAnswersManager->update($answer->getPoll_answer_id(), $answer->getArrayHash());
            } else {
                $this->pollsAnswersManager->add($answer->getArrayHash());
            }
        }
    }

    /**
     * @param PollEntity $poll
     */
    public function delete(PollEntity $poll)
    {
        $this->pollsManager->delete($poll->getPoll_id());
        $this->pollsAnswersManager->deleteByPoll($poll->getPoll_id());
        $this->pollsVotesManager->deleteByPoll($poll->getPoll_id());
    }
}
