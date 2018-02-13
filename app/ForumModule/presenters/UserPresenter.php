<?php

namespace App\ForumModule\Presenters;

use App\Controls\ChangePasswordControl;
use App\Controls\DeleteAvatarControl;
use App\Models\LanguagesManager;
use App\Models\UsersManager;
use Nette\Application\UI\Form;
use Nette\Utils\ArrayHash;

/**
 * Description of UserProfilePresenter
 *
 * @author rendi
 * @method UsersManager getManager()
 */
class UserPresenter extends Base\ForumPresenter
{

    /**
     * @var LanguagesManager $languageManager
     */
    private $languageManager;

    /**
     * UserPresenter constructor.
     *
     * @param UsersManager     $manager
     * @param LanguagesManager $languageManager
     */
    public function __construct(UsersManager $manager, LanguagesManager $languageManager)
    {
        parent::__construct($manager);

        $this->languageManager = $languageManager;
    }

    /**
     * @param Form      $form
     * @param ArrayHash $values
     */
    public function editUserFormSuccess(Form $form, ArrayHash $values)
    {
        $user = $this->getUser();


        $values->user_avatar = $this->getManager()->movieAvatar($values->user_avatar, $this->getContext()
                                                                                           ->getParameters()['wwwDir']);

        if ($user->isLoggedIn()) {
            $result = $this->getManager()->update($user->getId(), $values);
        } else {
            $result = $this->getManager()->add($values);
        }

        if ($result) {
            $this->flashMessage('User saved.', self::FLASH_MESSAGE_SUCCES);
        } else {
            $this->flashMessage('Nothing to change.', self::FLASH_MESSAGE_INFO);
        }

        $this->redirect('User:edit');
    }

    /**
     * @param Form      $form
     * @param ArrayHash $values
     */
    public function editUserOnValidate(Form $form, ArrayHash $values)
    {

    }

    /**
     *
     */
    public function actionLogout()
    {
        $this->getUser()->logout();

        $this->flashMessage('Successfuly logged out. ', self::FLASH_MESSAGE_SUCCES);
        $this->redirect('Index:default');
    }

    /**
     *
     */
    public function renderEdit()
    {
        $user = $this->getUser();

        if ($user->isLoggedIn()) {
            $userData = $this->getManager()->getById($this->getUser()->getId());
        }

        $this['editUserForm']->setDefaults($userData);

        $this->template->item = $userData;
    }

    /**
     * @param $user_id
     */
    public function renderPosts($user_id)
    {
        $this->template->posts = $this->getManager()->getPosts($user_id);
    }

    /**
     * @param $user_id
     */
    public function renderProfile($user_id)
    {
        $userData = $this->getManager()->getById($user_id);

        if (!$userData) {
            $this->error('User not found.');
        }

        $this->template->userData = $userData;
    }

    /**
     * @param $user_id
     */
    public function renderThanks($user_id)
    {
        $this->template->thanks = $this->getManager()->getThanks($user_id);
    }

    /**
     * @param $user_id
     */
    public function renderTopics($user_id)
    {
        $this->template->topics = $this->getManager()->getTopics($user_id);
    }

    /**
     * @return ChangePasswordControl
     */
    protected function createComponentChangePasswordControl()
    {
        return new ChangePasswordControl($this->getManager(), $this->getForumTranslator(), $this->getUser());
    }

    /**
     * @return DeleteAvatarControl
     */
    protected function createComponentDeleteAvatar()
    {
        return new DeleteAvatarControl($this->getManager(), $this->getContext(), $this->getUser(), $this->getForumTranslator());
    }

    /**
     * @return \App\Controls\BootstrapForm
     */
    protected function createComponentEditUserForm()
    {
        $form = $this->getBootStrapForm();

        $form->setTranslator($this->getForumTranslator());

        $form->addText('user_name', 'User name:');
        $form->addSelect('user_lang_id', 'User language:', $this->languageManager->getAllForSelect());
        $form->addUpload('user_avatar', 'User avatar:');

        $form->addSubmit('send', 'Send');

        $form->onSuccess[] = [
            $this,
            'editUserFormSuccess'
        ];
        $form->onValidate[] = [
            $this,
            'editUserOnValidate'
        ];

        return $form;
    }
}
