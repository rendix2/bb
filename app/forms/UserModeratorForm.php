<?php

namespace App\Forms;

use App\Database\EntityManagerDecorator;
use App\Model\Entity\ForumEntity;
use App\Models\ForumManager;
use App\Models\ModeratorManager;
use App\Presenters\Base\BasePresenter;
use Nette\Application\UI\Control;
use Nette\Application\UI\Form;
use Nette\Localization\ITranslator;
use Nette\Utils\ArrayHash;

/**
 * Description of UserModeratorForm
 *
 * @author rendix2
 * @package App\Forms
 */
class UserModeratorForm extends Control
{
    /**
     *
     * @var ITranslator $translator
     */
    private ITranslator $translator;
    
    /**
     *
     * @var ModeratorManager $moderatorsManager,
     */
    private ModeratorManager $moderatorsManager;
    
    /**
     *
     * @var ForumManager $forumsManager
     */
    private ForumManager $forumsManager;

    /**
     * UserModeratorForm constructor.
     *
     * @param ForumManager     $forumsManager
     * @param ModeratorManager $moderatorsManager
     * @param ITranslator       $translator
     */
    public function __construct(
        ForumManager     $forumsManager,
        ModeratorManager $moderatorsManager,
        ITranslator       $translator,
        private readonly EntityManagerDecorator $em
    ) {
        parent::__construct();

        $this->forumsManager     = $forumsManager;
        $this->moderatorsManager = $moderatorsManager;
        $this->translator        = $translator;
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
        
        $this->getTemplate()->forums       = $this->forumsManager->createForums($forums, 0);
        $this->getTemplate()->myModerators = $this->moderatorsManager->getPairsByLeft(
            $this->getPresenter()->getParameter('id')
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
    
    /**
     * @param Form      $form
     * @param ArrayHash $values
     */
    public function moderatorsSuccess(Form $form, ArrayHash $values): void
    {
        $moderators  = $form->getHttpData(Form::DataText, 'moderators[]');
        $user_id = $this->getPresenter()->getParameter('id');

        $this->moderatorsManager->addByLeft((int) $user_id, array_values($moderators));
        $this->getPresenter()->flashMessage('Forum was saved.', BasePresenter::FLASH_MESSAGE_SUCCESS);
        $this->getPresenter()->redirect('User:edit', $user_id);
    }
}
