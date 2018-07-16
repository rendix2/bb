<?php

namespace App\Forms;

/**
 * Description of ChangeUserNameForm
 *
 * @author rendi
 */
class ChangeUserNameForm extends \Nette\Application\UI\Control
{
    
    /**
     *
     * @var \App\Models\UsersManager $usersManager 
     */
    private $usersManager;

    /**
     *
     * @var \Nette\Security\User $user
     */
    private $user;

    /**
     * 
     * @param \App\Models\UsersManager $usersManager
     * @param \Nette\Security\User     $user
     */
    public function __construct(\App\Models\UsersManager $usersManager, \Nette\Security\User $user)
    {
        parent::__construct();
        
        $this->usersManager = $usersManager;
        $this->user         = $user;
    }

        /**
     * 
     * @return BootstrapForm
     */
    protected function createComponentChangeUserNameForm()
    {
        $form = self::createBootstrapForm();
        
        $form->addText('user_name', 'User name:');
        $form->addSubmit('send', 'Change user name');
        $form->onValidate[] = [$this, 'changeUserNameOnValidate'];
        $form->onSuccess[]  = [$this, 'changeUserNameSuccess'];
        
        return $form;
    }
    
    /**
     * 
     * @param Form      $form
     * @param ArrayHash $values
     */
    public function changeUserNameOnValidate(Form $form, ArrayHash $values)
    {
        if (count($this->usersManager->getByUserName($values->user_name))) {
            $form->addError('User already exists.');
        }
    }
    
    /**
     * 
     * @param Form      $form
     * @param ArrayHash $values
     */
    public function changeUserNameSuccess(Form $form, ArrayHash $values)
    {
        $result = $this->usersManager->update($this->user->getId(), $values);
        
        if ($result) {
            $this->flashMessage('User name was changed.', self::FLASH_MESSAGE_SUCCESS);
        } else {
            $this->flashMessage('Nothing to change.', self::FLASH_MESSAGE_INFO);
        }
    }    
    
}
