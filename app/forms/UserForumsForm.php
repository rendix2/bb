<?php

namespace App\Forms;

use App\Models\ForumsManager;
use App\Models\Users2ForumsManager;
use Nette\Application\UI\Control;
use Nette\Localization\ITranslator;

/**
 * Description of UserForumsForm
 *
 * @author rendix2
 */
class UserForumsForm extends Control
{
    /**
     * @var ForumsManager $forumsManager
     */
    private $forumsManager;
    
    /**
     *
     * @var Users2ForumsManager $users2ForumsManager,
     */
    private $users2ForumsManager;
    
    /**
     *
     * @var ITranslator $translator 
     */
    private $translator;   
    
    /**
     * 
     * @param ForumsManager       $forumsManager
     * @param Users2ForumsManager $users2ForumsManager
     * @param ITranslator         $translator
     */
    public function __construct(
        ForumsManager $forumsManager,
        Users2ForumsManager $users2ForumsManager,
        ITranslator $translator
    ) {        
        $this->forumsManager       = $forumsManager;
        $this->users2ForumsManager = $users2ForumsManager;
        $this->translator          = $translator;
    }
    
    public function render()
    {
        $this->template->setFile(__DIR__ . '/templates/userForumsForm.latte');
        $this->template->setTranslator($this->translator);
        
        $this->template->forums   = $this->forumsManager->createForums($this->forumsManager->getAllCached(), 0);
        $this->template->myForums = array_values($this->users2ForumsManager->getPairsByLeft($this->getPresenter()->getParameter('id')));
        
        $this->template->render();
    }
    
   /**
     * @return BootstrapForm
     */
    public function createComponentForumsForm()
    {
        $form = \App\Controls\BootstrapForm::create();

        $form->addSubmit('send_forum', 'Send');
        $form->onSuccess[] = [$this, 'forumsSuccess'];
        return $form;
    }

    /**
     * @param Form      $form
     * @param ArrayHash $values
     */
    public function forumsSuccess(Form $form, ArrayHash $values)
    {
        $forums  = $form->getHttpData($form::DATA_TEXT, 'forums[]');
        $user_id = $this->getParameter('id');

        $this->users2ForumsManager->addByLeft((int) $user_id, array_values($forums));
        $this->flashMessage('Forums saved.', self::FLASH_MESSAGE_SUCCESS);
        $this->redirect('User:edit', $user_id);
    }    
}
