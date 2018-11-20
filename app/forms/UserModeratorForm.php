<?php

namespace App\Forms;

use App\Controls\BootstrapForm;
use App\Models\ForumsManager;
use App\Models\ModeratorsManager;
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
    private $translator;
    
    /**
     *
     * @var ModeratorsManager $moderatorsManager,
     */
    private $moderatorsManager;
    
    /**
     *
     * @var ForumsManager $forumsManager
     */
    private $forumsManager;

    /**
     *
     * @param ForumsManager     $forumsManager
     * @param ModeratorsManager $moderatorsManager
     * @param ITranslator       $translator
     */
    public function __construct(
        ForumsManager     $forumsManager,
        ModeratorsManager $moderatorsManager,
        ITranslator       $translator
    ) {
        parent::__construct();

        $this->forumsManager     = $forumsManager;
        $this->moderatorsManager = $moderatorsManager;
        $this->translator        = $translator;
    }
    
    /**
     *
     */
    public function __destruct()
    {
        $this->translator        = null;
        $this->moderatorsManager = null;
        $this->forumsManager     = null;
    }

    /**
     *
     */
    public function render()
    {
        $sep = DIRECTORY_SEPARATOR;
        
        $this->template->setFile(__DIR__ . $sep . 'templates' . $sep . 'userModeratorForm.latte');
        $this->template->setTranslator($this->translator);
        
        $this->template->forums       = $this->forumsManager->createForums($this->forumsManager->getAllCached(), 0);
        $this->template->myModerators = $this->moderatorsManager->getPairsByLeft($this->getPresenter()->getParameter('id'));
        
        $this->template->render();
    }

    /**
     * @return BootstrapForm
     */
    public function createComponentModeratorsForm()
    {
        $form = BootstrapForm::create();
        
        $form->addSubmit('send_moderator', 'Send');
        $form->onSuccess[] = [$this, 'moderatorsSuccess'];

        return $form;
    }
    
    /**
     * @param Form      $form
     * @param ArrayHash $values
     */
    public function moderatorsSuccess(Form $form, ArrayHash $values)
    {
        $moderators  = $form->getHttpData($form::DATA_TEXT, 'moderators[]');
        $user_id = $this->presenter->getParameter('id');

        $this->moderatorsManager->addByLeft((int) $user_id, array_values($moderators));
        $this->presenter->flashMessage('Forum was saved.', BasePresenter::FLASH_MESSAGE_SUCCESS);
        $this->presenter->redirect('User:edit', $user_id);
    }
}
