<?php

namespace App\Models;

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
     * @param \App\Models\Entity\Poll $poll
     */
    public function add(Entity\Poll $poll)
    {
        $poll_data = $poll->getArrayHash();
        $poll_data->poll_time_to = $poll_data->poll_time_to->getTimestamp();
        
        $poll->poll_id = $this->pollsManager->add($poll_data);
        
        foreach ($poll->pollAnswers as $answer) {
            $answer->poll_id = $poll->poll_id;
            $this->pollsAnswersManager->add($answer->getArrayHash());
        }
    }

    /**
     * @param Entity\Poll $poll
     */
    public function delete(Entity\Poll $poll)
    {
        $this->pollsManager->delete($poll->poll_id);
        $this->pollsAnswersManager->deleteByPoll($poll->poll_id);
    }
}
