<?php

namespace App\Forms;

use App\Database\EntityManagerDecorator;
use App\Model\Entity\ForumEntity;
use App\Model\Entity\UserModeratorEntity;
use App\Model\Repository\ForumRepository;
use App\Model\Repository\UserModeratorRepository;
use App\Model\Repository\UserRepository;
use App\Models\ForumFacade;
use App\Presenters\Base\BasePresenter;
use Nette\Application\UI\Control;
use Nette\Application\UI\Form;
use Nette\Localization\Translator;
use Nette\Utils\ArrayHash;

/**
 * Description of UserModeratorForm
 *
 * @author rendix2
 * @package App\Forms
 */
class UserModeratorForm extends Control
{

    public function __construct(
        private readonly Translator             $translator,
        private readonly EntityManagerDecorator $em,

        private readonly ForumRepository         $forumRepository,
        private readonly UserRepository          $userRepository,
        private readonly UserModeratorRepository $userModeratorRepository,


        private readonly ForumFacade $forumFacade,
    ) {
    }

    /**
     * UserModeratorForm render.
     */
    public function render()
    {
        $sep = DIRECTORY_SEPARATOR;

        $this->getTemplate()->setFile(__DIR__ . $sep . 'templates' . $sep . 'userModeratorForm.latte');
        $this->getTemplate()->setTranslator($this->translator);

        $forums = $this->em
            ->getRepository(ForumEntity::class)
            ->findAll();

        $this->getTemplate()->forums       = $this->forumFacade->createForums($forums, 0);
        $this->getTemplate()->myModerators = $this->userModeratorRepository
            ->findBy(
                [
                    'user' => $this->getPresenter()->getParameter('id'),
                ],
            );

        $this->getTemplate()->render();
    }

    public function createComponentModeratorsForm(): \Contributte\FormsBootstrap\BootstrapForm
    {
        $form = new \Contributte\FormsBootstrap\BootstrapForm();
        
        $form->addSubmit('send_moderator', 'Send');
        $form->onSuccess[] = [$this, 'moderatorsSuccess'];

        return $form;
    }
    
    public function moderatorsSuccess(Form $form, ArrayHash $values): void
    {
        $moderators  = $form->getHttpData(Form::DataText, 'moderators[]');
        $user_id = $this->getPresenter()->getParameter('id');

        $userEntity = $this->userRepository
            ->findOneBy(
            [
                'id' => $user_id,
            ]
        );

        foreach ($moderators as $moderator) {
            $forumEntity = $this->forumRepository
                ->findOneBy(
                [
                    'id' => $moderator,
                ]
            );

            $userModeratorEntity = new UserModeratorEntity();
            $userModeratorEntity->user = $userEntity;
            $userModeratorEntity->forum = $forumEntity;

            $this->em->persist($userModeratorEntity);
        }

        $this->em->flush();

        $this->getPresenter()->flashMessage('Forum was saved.', BasePresenter::FLASH_MESSAGE_SUCCESS);
        $this->getPresenter()->redirect('User:edit', $user_id);
    }
}
