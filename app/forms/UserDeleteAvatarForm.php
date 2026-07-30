<?php

namespace App\Forms;

use App\Model\Repository\UserRepository;
use App\Models\UsersManager;
use App\Presenters\Base\BasePresenter;
use App\services\AvatarService;
use App\Settings\Avatars;
use Nette\Application\UI\Control;
use Nette\Application\UI\Form;
use Nette\Localization\ITranslator;
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

    /**
     * @var UsersManager $userManager
     */
    private UsersManager $userManager;

    /**
     * @var User $user
     */
    private User $user;

    /**
     * @var ITranslator $translator
     */
    private ITranslator $translator;

    public function __construct(
        UsersManager $userManager,
        User $user,
        ITranslator $translator,

        private readonly UserRepository $userRepository,

        private readonly AvatarService $avatarService,
    ) {
        parent::__construct();

        $this->userManager = $userManager;
        $this->user = $user;
        $this->translator = $translator;
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
            $userEntity = $this->userRepository
                ->findOneBy(
                    [
                        'id' => $this->user->getId(),
                    ],
                );

            if ($userEntity->user_avatar) {
                $this->avatarService->removeAvatarFile($userEntity->user_avatar);
                $this->userManager->update($userEntity->id, ArrayHash::from(['user_avatar' => null]));
                $this->flashMessage('Avatar was deleted.', BasePresenter::FLASH_MESSAGE_SUCCESS);
                $this->redirect('this');
            }
        }
    }
}
