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

    /**
     * @param int $item_id
     */
    public function delete($item_id)
    {
        $this->pollsManager->delete($item_id);
    }
}
