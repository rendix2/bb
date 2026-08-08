<?php

namespace App\Forms;

use App\Database\EntityManagerDecorator;
use App\Model\Repository\UserRepository;
use App\Presenters\Base\BasePresenter;
use App\services\AvatarService;
use Nette\Application\UI\Control;
use Nette\Application\UI\Form;
use Nette\Localization\Translator;
use Nette\Security\User;
use Nette\Utils\ArrayHash;

/**
 * Description of DeleteAvatarControl
 *
 * @author rendix2
 * @package App\Forms
 */
class UserDeleteAvatarForm extends Control
{

    public function __construct(
        private readonly EntityManagerDecorator $em,
        private readonly User $user,
        private readonly Translator $translator,

        private readonly UserRepository $userRepository,

        private readonly AvatarService $avatarService,
    ) {
    }

    /**
     * renders avatars delete control
     */
    public function render(): void
    {
        $this['deleteAvatar']->render();
    }

    protected function createComponentDeleteAvatar(): \Contributte\FormsBootstrap\BootstrapForm
    {
        $form = new \Contributte\FormsBootstrap\BootstrapForm();
        $form->setTranslator($this->translator);

        $form->addCheckbox('delete_avatar', 'Delete avatar');
        $form->addSubmit('send', 'Delete avatar');
        $form->onSuccess[] = [$this, 'deleteAvatarSuccess'];

        return $form;
    }

    /**
     * @param Form $form
     * @param ArrayHash $values
     */
    public function deleteAvatarSuccess(Form $form, ArrayHash $values): void
    {
        if (isset($values->delete_avatar) && $values->delete_avatar === true) {
            $userEntity = $this->userRepository->findOneByNetteUser($this->user);

            if ($userEntity->user_avatar) {
                $this->avatarService->removeAvatarFile($userEntity->user_avatar);

                $userEntity->avatar = null;

                $this->em->persist($userEntity);
                $this->em->flush();

                $this->flashMessage('Avatar was deleted.', BasePresenter::FLASH_MESSAGE_SUCCESS);
                $this->redirect('this');
            }
        }
    }
}
