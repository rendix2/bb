<?php

namespace App\AdminModule\Presenters;

use App\Authenticator;
use App\Controls\BootstrapForm;
use App\Controls\ChangePasswordControl;
use App\Controls\DeleteAvatarControl;
use App\Models\ForumsManager;
use App\Models\GroupsManager;
use App\Models\LanguagesManager;
use App\Models\Users2ForumsManager;
use App\Models\Users2GroupsManager;
use App\Models\UsersManager;
use Nette\Application\UI\Form;
use Nette\Utils\ArrayHash;

/**
 * Description of UserPresenter
 *
 * @author rendi
 */
class UserPresenter extends Base\AdminPresenter
{
    /**
     * @var LanguagesManager $languagesManager
     */
    private $languagesManager;

    /**
     * @var Users2GroupsManager $group2User
     */
    private $group2User;

    /**
     * @var GroupsManager $groupManager
     */
    private $groupManager;

    /**
     * @var ForumsManager $forumsManager
     */
    private $forumsManager;

    /**
     * @var Users2ForumsManager $users2Forums
     */
    private $users2Forums;

    /**
     * UserPresenter constructor.
     *
     * @param UsersManager $manager
     */
    public function __construct(UsersManager $manager)
    {
        parent::__construct($manager);
    }

    /**
     * @return DeleteAvatarControl
     */
    public function createComponentDeleteAvatar()
    {
        return new DeleteAvatarControl($this->getManager(), $this->getContext(), $this->getUser(), $this->getAdminTranslator());
    }

    /**
     * @return BootstrapForm
     */
    public function createComponentForumsForm()
    {
        $form = new BootstrapForm();

        $form->addSubmit('send_forum', 'Send');
        $form->onSuccess[] = [
            $this,
            'forumsSuccess'
        ];

        return $form;
    }

    /**
     * @param Form      $form
     * @param ArrayHash $values
     */
    public function forumsSuccess(Form $form, ArrayHash $values)
    {
        $forums  = $form->getHttpData($form::DATA_TEXT, 'forums[]');
        $user_id = $this->getParameter('id');

        $this->users2Forums->addByLeft((int)$user_id, array_values($forums));
        $this->flashMessage('Forums saved.', self::FLASH_MESSAGE_SUCCES);
        $this->redirect('User:edit', $user_id);
    }

    /**
     * @param Form      $form
     * @param ArrayHash $values
     */
    public function groupSuccess(Form $form, ArrayHash $values)
    {
        $groups  = $form->getHttpData($form::DATA_TEXT, 'group[]');
        $user_id = $this->getParameter('id');

        $this->group2User->addByLeft((int)$user_id, array_values($groups));
        $this->flashMessage('Groups saved.', self::FLASH_MESSAGE_SUCCES);
        $this->redirect('User:edit', $user_id);
    }

    /**
     * @param ForumsManager $forumsManager
     */
    public function injectForumsManager(ForumsManager $forumsManager)
    {
        $this->forumsManager = $forumsManager;
    }

    /**
     * @param Users2GroupsManager $group2User
     */
    public function injectGroup2UserManager(Users2GroupsManager $group2User)
    {
        $this->group2User = $group2User;
    }

    /**
     * @param LanguagesManager $languagesManager
     */
    public function injectLanguagesManager(LanguagesManager $languagesManager)
    {
        $this->languagesManager = $languagesManager;
    }

    /**
     * @param Users2ForumsManager $users2Forums
     */
    public function injectUsers2ForumsManager(Users2ForumsManager $users2Forums)
    {
        $this->users2Forums = $users2Forums;
    }

    /**
     * @param GroupsManager $groupManager
     */
    public function injectGroupManager(GroupsManager $groupManager)
    {
        $this->groupManager = $groupManager;
    }

    /**
     * @param null $id
     */
    public function renderEdit($id = null)
    {
        parent::renderEdit($id);

        $this->template->groups   = $this->groupManager->getAllCached();
        $this->template->myGroups = array_values($this->group2User->getByLeftPairs($id));

        $this->template->forums   = $this->forumsManager->createForums($this->forumsManager->getAllCached(), 0);
        $this->template->myForums = array_values($this->users2Forums->getByLeftPairs($id));
    }

    /**
     * @return ChangePasswordControl
     */
    protected function createComponentChangePasswordControl()
    {
        return new ChangePasswordControl($this->getManager(), $this->getAdminTranslator(), $this->getUser());
    }

    /**
     * @return BootStrapForm
     */
    protected function createComponentEditForm()
    {
        $form = $this->getBootStrapForm();
        $form->setTranslator($this->getAdminTranslator());

        $form->addGroup('user_data');
        $form->addText('user_name', 'User name:')->setRequired(true);
        $form->addEmail('user_email', 'User mail:')->setRequired(true);
        $form->addGroup('user_settings');
        $form->addSelect('user_role_id', 'User role:', Authenticator::ROLES)->setTranslator(null);
        $form->addSelect('user_lang_id', 'User language:', $this->languagesManager->getAllForSelect());
        $form->addTextArea('user_signature', 'User signature:');
        //$form->addUpload('user_avatar', 'User avatar:');

        $form->addCheckbox('user_active', 'User active:');

        return $this->addSubmitB($form);
    }

    /**
     * @return BootstrapForm
     */
    protected function createComponentGroupFrom()
    {
        $form = new BootstrapForm();

        $form->addSubmit('send_group', 'Send');
        $form->onSuccess[] = [
            $this,
            'groupSuccess'
        ];

        return $form;
    }

}
