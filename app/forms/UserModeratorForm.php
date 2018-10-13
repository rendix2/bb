<?php

namespace App\Forms;

use App\Controls\BootstrapForm;
use App\Models\ForumsManager;
use App\Models\ModeratorsManager;
use Nette\Application\UI\Control;
use Nette\Localization\ITranslator;

/**
 * Description of UserModeratorForm
 *
 * @author rendix2
 */
class UserModeratorForm extends Control
{
    private $translator;
    
    private $moderatorsManager;
    
    private $forumsManager;

    
    public function __construct(
            ForumsManager $forumsManager,
            ModeratorsManager $moderatorsManager,
            ITranslator $translator
    ) {
        $this->forumsManager     = $forumsManager;
        $this->moderatorsManager = $moderatorsManager;
        $this->translator        = $translator;
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
        $user_id = $this->getParameter('id');

        $this->moderatorsManager->addByLeft((int) $user_id, array_values($moderators));
        $this->flashMessage('Forums saved.', self::FLASH_MESSAGE_SUCCESS);
        $this->redirect('User:edit', $user_id);
    }
    
}
