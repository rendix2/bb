<?php

namespace App\Forms;

use App\Database\EntityManagerDecorator;
use App\Model\Repository\UserRepository;
use App\Presenters\Base\BasePresenter;
use Doctrine\DBAL\Exception as DbalException;
use Nette\Application\UI\Form;
use Nette\Security\User;
use \Nette\Application\UI\Control;
use Nette\Utils\ArrayHash;

class UserChangeUserNameForm extends Control
{

    public function __construct(
        private readonly User $user,
        private readonly UserRepository $userRepository,

        private readonly EntityManagerDecorator $em
    )
    {
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
        $userEntity = $this->userRepository
            ->findOneByNetteUser($this->user);

        $userEntity->username = $values->username;

        try {
            $this->em->persist($userEntity);
            $this->em->flush();

            $this->getPresenter()->flashMessage('User name was changed.', BasePresenter::FLASH_MESSAGE_SUCCESS);
            $this->getPresenter()->redrawControl('flashes;');
        } catch (DbalException $exception) {
           $this->getPresenter()->flashMessage($exception->getMessage(), 'danger');
           $this->getPresenter()->redrawControl('danger');
        }
    }
}
