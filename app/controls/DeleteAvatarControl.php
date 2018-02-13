<?php

namespace App\Controls;

use App\Models\UsersManager;
use App\Presenters\Base\BasePresenter;
use Nette\Application\UI\Control;
use Nette\Application\UI\Form;
use Nette\DI\Container;
use Nette\Localization\ITranslator;
use Nette\Security\User;
use Nette\Utils\ArrayHash;
use Nette\Utils\FileSystem;

/**
 * Description of DeleteAvatarControl
 *
 * @author rendi
 */
class DeleteAvatarControl extends Control
{

    /**
     * @var UsersManager $userManager
     */
    private $userManager;
    /**
     * @var Container $container
     */
    private $container;
    /**
     * @var User $user
     */
    private $user;
    /**
     * @var ITranslator $translator
     */
    private $translator;


    /**
     * DeleteAvatarControl constructor.
     *
     * @param UsersManager $userManager
     * @param Container    $container
     * @param User         $user
     * @param ITranslator  $translator
     */
    public function __construct(UsersManager $userManager, Container $container, User $user, ITranslator $translator)
    {
        parent::__construct();

        $this->userManager = $userManager;
        $this->container   = $container;
        $this->user        = $user;
        $this->translator  = $translator;

    }

    /**
     * @param Form      $form
     * @param ArrayHash $values
     */
    public function deleteAvatarSuccess(Form $form, ArrayHash $values)
    {
        if (isset($values->delete_avatar) && $values->delete_avatar === true) {
            $user = $this->userManager->getById($this->user->getId());

            if ($user->user_avatar) {
                $wwwDir = $this->container->getParameters()['wwwDir'];
                $separator = DIRECTORY_SEPARATOR;

                $path = $wwwDir . $separator . 'avatars' . $separator . $user->user_avatar;

                FileSystem::delete($path);
                $this->userManager->update($user->user_id, ArrayHash::from(['user_avatar' => null]));
                $this->flashMessage('Avatar was deleted.', BasePresenter::FLASH_MESSAGE_SUCCES);
                $this->redirect('this');
            }
        }
    }

    /**
     *
     */
    public function render()
    {
        $this->template->setFile(__DIR__ . '/templates/deleteAvatar/deleteAvatar.latte');
        $this->template->render();
    }

    /**
     * @return BootstrapForm
     */
    protected function createComponentDeleteAvatar()
    {
        $form = new BootstrapForm();
        $form->setTranslator($this->translator);

        $form->addCheckbox('delete_avatar', 'Delete avatar');
        $form->addSubmit('send', 'Delete avatar');
        $form->onSuccess[] = [
            $this,
            'deleteAvatarSuccess'
        ];

        return $form;
    }

}
