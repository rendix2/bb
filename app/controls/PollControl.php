<?php

namespace App\Controls;

use App\Database\EntityManagerDecorator;
use App\Model\Repository\PollAnswerRepository;
use App\Model\Repository\PollRepository;
use App\Model\Repository\PollVoteRepository;
use App\Model\Repository\UserRepository;
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
     * @var User $user ;
     */
    private User $user;

    /**
     *
     * @var ITranslator $translator
     */
    private ITranslator $translator;

    public function __construct(
        User                                  $user,
        ITranslator                           $translator,

        private readonly PollRepository       $pollRepository,
        private readonly PollAnswerRepository $pollAnswerRepository,
        private readonly PollVoteRepository   $pollVoteRepository,

        private readonly UserRepository $userRepository,

        private readonly EntityManagerDecorator $em,
    )
    {
        parent::__construct();

        $this->user = $user;
        $this->translator = $translator;
    }

    public function handleVote(int $poll_id, int $poll_answer_id): void
    {
        $pollEntity = $this->pollRepository
            ->findOneBy(
                [
                    'id' => $poll_id,
                ]
            );

        $pollAnswerEntity = $this->pollAnswerRepository
            ->findOneBy(
                [
                    'id' => $poll_answer_id,
                ]
            );

        $userEntity = $this->userRepository
            ->findOneBy(
                [
                    'id' => $this->user->getId(),
                ]
            );

        $pollVoteEntity = new \App\Model\Entity\PollVoteEntity();
        $pollVoteEntity->poll = $pollEntity;
        $pollVoteEntity->pollAnswer = $pollAnswerEntity;
        $pollVoteEntity->user = $userEntity;
        
        try {
            $this->em->persist($pollVoteEntity);
            $this->em->flush();

            $this->presenter->flashMessage('Vote was saved.', BasePresenter::FLASH_MESSAGE_SUCCESS);
            $this->presenter->redirect('this');
        } catch (UniqueConstraintViolationException $e) {
            $this->presenter->flashMessage('You have already voted.', BasePresenter::FLASH_MESSAGE_WARNING);
        }
    }

    public function render(): void
    {
        $sep = DIRECTORY_SEPARATOR;

        $template = $this->template->setFile(__DIR__ . $sep . 'templates' . $sep . 'poll' . $sep . 'poll.latte');
        $template->setTranslator($this->translator);
        $presenter = $this->presenter;

        $poll = $this->pollRepository->getByTopicId($presenter->getParameter('topic_id'));

        if ($poll) {
            $pollAnswers = $this->pollAnswerRepository->findByPollId($poll->id);
            $pollVotes = $this->pollVoteRepository->findByPollId($poll->id);

            foreach ($pollAnswers as $answer) {
                $answer->count = 0;

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

            $template->poll = $poll;
            $template->pollAnswers = $pollAnswers;
            $template->pollVotes = $pollVotes;
            $template->canVote = $canVote;

            $template->render();
        }
    }
}
