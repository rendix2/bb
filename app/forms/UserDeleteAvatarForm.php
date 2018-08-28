<?php

namespace App\Forms;

use App\Controls\BootstrapForm;
use App\Models\UsersManager;
use App\Presenters\Base\BasePresenter;
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
 */
class UserDeleteAvatarForm extends Control
{

    /**
     * @var UsersManager $userManager
     */
    private $userManager;
    
    /**
     * @var User $user
     */
    private $user;
    
    /**
     * @var ITranslator $translator
     */
    private $translator;
    
    /**
     * @var Avatars $avatars
     */
    public $avatars;

    /**
     * DeleteAvatarControl constructor.
     *
     * @param UsersManager $userManager
     * @param Avatars      $avatars
     * @param User         $user
     * @param ITranslator  $translator
     */
    public function __construct(UsersManager $userManager, Avatars $avatars, User $user, ITranslator $translator)
    {
        parent::__construct();

        $this->userManager = $userManager;
        $this->avatars     = $avatars;
        $this->user        = $user;
        $this->translator  = $translator;
    }

    /**
     * renders avatars delete control
     */
    public function render()
    {
        $this['deleteAvatar']->render();
    }

    /**
     * @return BootstrapForm
     */
    protected function createComponentDeleteAvatar()
    {
        $form = BootstrapForm::create();
        $form->setTranslator($this->translator);

        $form->addCheckbox('delete_avatar', 'Delete avatar');
        $form->addSubmit('send', 'Delete avatar');
        $form->onSuccess[] = [$this,'deleteAvatarSuccess'];

        return $form;
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
                $this->userManager->removeAvatarFile($user->user_avatar);
                $this->userManager->update($user->user_id, ArrayHash::from(['user_avatar' => null]));
                $this->flashMessage('Avatar was deleted.', BasePresenter::FLASH_MESSAGE_SUCCESS);
                $this->redirect('this');
            }
        }
    }
}
