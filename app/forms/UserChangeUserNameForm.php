<?php

namespace App\Forms;

use App\Model\Repository\UserRepository;
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

    public function __construct(
        private readonly UsersManager $usersManager,
        private readonly User $user,
        private readonly UserRepository $userRepository,
    )
    {
        parent::__construct();

    }

    public function render(): void
    {
        $this['changeUserNameForm']->render();
    }

    protected function createComponentChangeUserNameForm(): \Contributte\FormsBootstrap\BootstrapForm
    {
        $form = new \Contributte\FormsBootstrap\BootstrapForm();
        
        $form->addText('username', 'User name:');
        $form->addSubmit('send', 'Change user name');
        $form->onValidate[] = [$this, 'changeUserNameOnValidate'];
        $form->onSuccess[]  = [$this, 'changeUserNameSuccess'];
        
        return $form;
    }
    
    public function changeUserNameOnValidate(Form $form, ArrayHash $values): void
    {
        $userEntity = $this->userRepository->findOneByUsername($values->username);

        if ($userEntity) {
            $form->addError('User already exists.');
        }
    }
    
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
