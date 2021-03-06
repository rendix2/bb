<?php
namespace App\Controls;

use App\Models\Entity\PollVoteEntity;
use App\Models\PollsFacade;
use App\Presenters\Base\BasePresenter;
use Dibi\UniqueConstraintViolationException;
use Nette\Application\UI\Control;
use Nette\Localization\ITranslator;
use Nette\Security\User;

/**
 * Description of PollControl
 *
 * @author rendix2
 * @package App\Controls
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
    
    /**
     *
     * @var ITranslator $translator
     */
    private $translator;

    /**
     *
     * @param PollsFacade $pollsFacade
     * @param User $user
     * @param ITranslator $translator
     */
    public function __construct(
        PollsFacade $pollsFacade,
        User        $user,
        ITranslator $translator
    ) {
        parent::__construct();
        
        $this->user        = $user;
        $this->pollsFacade = $pollsFacade;
        $this->translator  = $translator;
    }
    
    /**
     * PollControl destructor.
     */
    public function __destruct()
    {
        $this->pollsFacade = null;
        $this->user        = null;
        $this->translator  = null;
    }

    /**
     *
     * @param int $poll_id
     * @param int $poll_answer_id
     */
    public function handleVote($poll_id, $poll_answer_id)
    {
        $pollVote = new PollVoteEntity();
        $pollVote->setPoll_id($poll_id)
                 ->setPoll_answer_id($poll_answer_id)
                 ->setPoll_user_id($this->user->id);
        
        try {
            $this->pollsFacade->getPollsVotesManager()->add($pollVote->getArrayHash());
            $this->presenter->flashMessage('Vote was saved.', BasePresenter::FLASH_MESSAGE_SUCCESS);
            $this->presenter->redirect('this');
        } catch (UniqueConstraintViolationException $e) {
            $this->presenter->flashMessage('You have already voted.', BasePresenter::FLASH_MESSAGE_WARNING);
        }
    }

    /**
     * PollControl render.
     */
    public function render()
    {
        $sep = DIRECTORY_SEPARATOR;

        $template  = $this->template->setFile(__DIR__ . $sep . 'templates' . $sep . 'poll' . $sep. 'poll.latte');
        $template->setTranslator($this->translator);
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
            
            $template->poll        = $poll;
            $template->pollAnswers = $pollAnswers;
            $template->pollVotes   = $pollVotes;
            $template->canVote     = $canVote;
        
            $template->render();
        }
    }
}
