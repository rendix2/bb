<?php

namespace App\Models;

use App\Models\Entity\Poll;

/**
 * Description of PollsFacade
 *
 * @author rendix2
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
        PollsManager $pollsManager,
        PollsAnswersManager $pollsAnswersManager,
        PollsVotesManager $pollsVotesManager
    ) {
        $this->pollsManager        = $pollsManager;
        $this->pollsAnswersManager = $pollsAnswersManager;
        $this->pollsVotesManager   = $pollsVotesManager;
    }
    
    public function __destruct() {
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
     * @param Poll $poll
     */
    public function add(Poll $poll)
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
     * @param Poll $poll
     */
    public function update(Poll $poll)
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
     * @param Poll $poll
     */
    public function delete(Poll $poll)
    {
        $this->pollsManager->delete($poll->getPoll_id());
        $this->pollsAnswersManager->deleteByPoll($poll->getPoll_id());
        $this->pollsVotesManager->deleteByPoll($poll->getPoll_id());
    }
}
