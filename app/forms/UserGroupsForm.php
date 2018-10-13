<?php

namespace App\Forms;

use App\Models\GroupsManager;
use App\Models\Users2GroupsManager;
use Nette\Application\UI\Control;
use Nette\Localization\ITranslator;
/**
 * Description of UserGroupsForm
 *
 * @author rendi
 */
class UserGroupsForm extends Control
{

    /**
     *
     * @var GroupsManager $groupsManager
     */
    private $groupsManager;

    /**
     *
     * @var Users2GroupsManager 
     */
    private $users2GroupsManager;
    
    /**
     *
     * @var ITranslator $translator 
     */
    private $translator;
    
    /**
     * 
     * @param Users2GroupsManager $users2GroupsManager
     */
    public function __construct(
        GroupsManager $groupsManager,
        Users2GroupsManager $users2GroupsManager,
        ITranslator $translator
    ){
        $this->groupsManager       = $groupsManager;
        $this->users2GroupsManager = $users2GroupsManager;
        $this->translator          = $translator;
    }

    /**
     * 
     */
    public function render()
    {   
        $this->template->setFile(__DIR__ . '/templates/userGroupsForm.latte');
        $this->template->setTranslator($this->translator);
        
        $this->template->groups   = $this->groupsManager->getAllCached();
        $this->template->myGroups = array_values($this->users2GroupsManager->getPairsByLeft($this->getPresenter()->getParameter('id')));
        
        $this->template->render();
    }

    /**
     * @return BootstrapForm
     */
    protected function createComponentGroupFrom()
    {
        $form = \App\Controls\BootstrapForm::create();

        $form->addSubmit('send_group', 'Send');
        $form->onSuccess[] = [$this, 'groupSuccess'];

        return $form;
    }
    
    /**
     * @param Form      $form
     * @param ArrayHash $values
     */
    public function groupSuccess(Form $form, ArrayHash $values)
    {
        $groups  = $form->getHttpData($form::DATA_TEXT, 'group[]');
        $user_id = $this->getParameter('id');

        $this->users2GroupsManager->addByLeft((int) $user_id, array_values($groups));
        $this->flashMessage('Groups saved.', self::FLASH_MESSAGE_SUCCESS);
        $this->redirect('User:edit', $user_id);
    }
    
}
