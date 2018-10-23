<?php
namespace App\Controls;

use App\Models\PollsFacade;
use Nette\Application\UI\Control;
use Nette\Security\User;

/**
 * Description of PollControl
 *
 * @author rendix2
 */
class PollControl extends Control
{

    /**
     *
     * @var PollsFacade $pollsFacade
     */
    private $pollsFacade;
        
    /**
     *
     * @var User $user;
     */
    private $user;
    
    public function __construct(PollsFacade $pollsFacade, User $user)
    {
        parent::__construct();
        
        $this->user        = $user;
        $this->pollsFacade = $pollsFacade;
    }
    
    /**
     * 
     * @param int $poll_id
     * @param int $poll_answer_id
     */
    public function handleVote($poll_id, $poll_answer_id)
    {
        $pollVote = new \App\Models\Entity\PollVote(null, $poll_id, $poll_answer_id, $this->user->getId());
        
        try {
            $this->pollsFacade->getPollsVotesManager()->add($pollVote->getArrayHash());
            $this->presenter->flashMessage('Vote was saved.', \App\Presenters\Base\BasePresenter::FLASH_MESSAGE_SUCCESS);
            $this->presenter->redirect('this');
        } catch (\Dibi\UniqueConstraintViolationException $e) {
            $this->presenter->flashMessage('You have already voted.', \App\Presenters\Base\BasePresenter::FLASH_MESSAGE_WARNING);
        }
    }

    public function render()
    {
        $sep = DIRECTORY_SEPARATOR;

        $template  = $this->template->setFile(__DIR__ . $sep . 'templates' . $sep . 'poll' . $sep. 'poll.latte');
        $presenter = $this->presenter;
        
        $poll = $this->pollsFacade->getPollsManager()->getByTopic($presenter->getParameter('topic_id'));

        if ($poll) {
            $pollAnswers = $this->pollsFacade->getPollsAnswersManager()->getAllByPoll($poll->poll_id);
            $pollVotes   = $this->pollsFacade->getPollsVotesManager()->getAllByPoll($poll->poll_id);

            foreach ($pollAnswers as $answer) {
                $answer->count   = 0;
               
                foreach ($pollVotes as $vote) {
                    if ($vote->poll_answer_id === $answer->poll_answer_id) {
                        $answer->count += 1;
                    }    
                }
            }
           
            $canVote = true;
            
            foreach ($pollVotes as $vote) {
                if ($vote->poll_user_id === $this->user->id) {
                    $canVote = false;
                    break;
                }       
            }           
       }
       
       $template->poll        = $poll;
       $template->pollAnswers = $pollAnswers;
       $template->pollVotes   = $pollVotes;
       $template->canVote     = $canVote;
        
        $template->render();
    }
    
}
