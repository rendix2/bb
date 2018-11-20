<?php

namespace App\Forms;

use App\Controls\BootstrapForm;
use App\Models\UsersManager;
use App\Presenters\Base\BasePresenter;
use Nette\Application\UI\Control;
use Nette\Application\UI\Form;
use Nette\Security\User;
use Nette\Utils\ArrayHash;

/**
 * Description of ChangeUserNameForm
 *
 * @author rendix2
 * @package App\Forms
 */
class UserChangeUserNameForm extends Control
{
    
    /**
     *
     * @var UsersManager $usersManager
     */
    private $usersManager;

    /**
     *
     * @var User $user
     */
    private $user;

    /**
     *
     * @param UsersManager $usersManager
     * @param User         $user
     */
    public function __construct(UsersManager $usersManager, User $user)
    {
        parent::__construct();
        
        $this->usersManager = $usersManager;
        $this->user         = $user;
    }
    
    /**
     * 
     */
    public function __destruct()
    {
        $this->usersManager = null;
        $this->user         = null;
    }

    /**
     * 
     */
    public function render()
    {
        $this['changeUserNameForm']->render();
    }

    /**
     *
     * @return BootstrapForm
     */
    protected function createComponentChangeUserNameForm()
    {
        $form = BootstrapForm::create();
        
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
        if (count($this->usersManager->checkUserNameExists($values->user_name))) {
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
        $result = $this->usersManager->update($this->user->id, $values);
        
        if ($result) {
            $this->presenter->flashMessage('User name was changed.', BasePresenter::FLASH_MESSAGE_SUCCESS);
        } else {
            $this->presenter->flashMessage('Nothing to change.', BasePresenter::FLASH_MESSAGE_INFO);
        }
    }
}
