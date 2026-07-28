<?php

namespace App\Forms;

use App\Models\UsersManager;
use App\Presenters\Base\BasePresenter;
use Nette\Application\UI\Form;
use Nette\Security\User;
use \Nette\Application\UI\Control;
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
     * @param UsersManager $usersManager
     * @param User         $user
     */
    public function __construct(private readonly UsersManager $usersManager, private readonly User $user)
    {
        parent::__construct();

    }

    /**
     * UserChangeUserNameForm render.
     */
    public function render(): void
    {
        $this['changeUserNameForm']->render();
    }

    protected function createComponentChangeUserNameForm(): \Contributte\FormsBootstrap\BootstrapForm
    {
        $form = new \Contributte\FormsBootstrap\BootstrapForm();
        
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
    public function changeUserNameOnValidate(Form $form, ArrayHash $values): void
    {
        if ($this->usersManager->checkUserNameExists($values->user_name)) {
            $form->addError('User already exists.');
        }
    }
    
    /**
     *
     * @param Form      $form
     * @param ArrayHash $values
     */
    public function changeUserNameSuccess(Form $form, ArrayHash $values): void
    {
        $result = $this->usersManager->update($this->user->getId(), $values);
        
        if ($result) {
            $this->presenter->flashMessage('User name was changed.', BasePresenter::FLASH_MESSAGE_SUCCESS);
        } else {
            $this->presenter->flashMessage('Nothing to change.', BasePresenter::FLASH_MESSAGE_INFO);
        }
    }
}
