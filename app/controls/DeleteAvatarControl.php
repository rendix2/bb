<?php

/**
 * Description of DeleteAvatarControl
 *
 * @author rendi
 */
class DeleteAvatarControl extends \Nette\Application\UI\Control {

    private $userManager;
    private $container;
    private $user;
    private $translator;


    public function __construct(\App\Models\UsersManager $userManager, \Nette\DI\Container $container, Nette\Security\User $user, \Nette\Localization\ITranslator $translator) {
        parent::__construct();
        
        $this->userManager = $userManager;
        $this->container = $container;
        $this->user = $user;
        $this->translator = $translator;
        
    }

    protected function createComponentDeleteAvatar() {
        $form = new \App\Controls\BootstrapForm();
        $form->setTranslator($this->translator);

        $form->addCheckbox('delete_avatar', 'Delete avatar');
        $form->addSubmit('send', 'Delete avatar');
        $form->onSuccess[] = [$this, 'deleteAvatarSuccess'];
        return $form;
    }

    public function deleteAvatarSuccess(\Nette\Application\UI\Form $form, \Nette\Utils\ArrayHash $values) {        
        if (isset($values->delete_avatar) && $values->delete_avatar === true) {
            $user = $this->userManager->getById($this->user->getId());

            if ($user->user_avatar) {
                $wwwDir = $this->container->getParameters()['wwwDir'];
                $separator = DIRECTORY_SEPARATOR;

                $path = $wwwDir . $separator . 'avatars' . $separator . $user->user_avatar;

                \Nette\Utils\FileSystem::delete($path);
                $this->userManager->update($user->user_id, \Nette\Utils\ArrayHash::from(['user_avatar' => null]));
                $this->flashMessage('Avatar was deleted.', \App\Presenters\Base\BasePresenter::FLASH_MESSAGE_SUCCES);
                $this->redirect('this');
            }
        }
    }
    
    public function render() {
        $this->template->setFile(__DIR__ . '/templates/deleteAvatar/deleteAvatar.latte');
        $this->template->render();
    }    

}
