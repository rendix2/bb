<?php

namespace App\AdminModule\Presenters;

/**
 * Description of UserPresenter
 *
 * @author rendi
 */
class UserPresenter extends Base\AdminPresenter {

    private $rolesManager;
    private $languagesManager;
    private $group2User;
    private $groupManager;
    private $forumsManager;
    private $users2Forums;

    public function __construct(\App\Models\UsersManager $manager) {
        parent::__construct($manager);
    }

    public function injectRolesManager(\App\Models\RolesManager $rolesManager) {
        $this->rolesManager = $rolesManager;
    }

    public function injectLanguagesManager(\App\Models\LanguagesManager $languagesManager) {
        $this->languagesManager = $languagesManager;
    }

    public function injectgGroupManager(\App\Models\GroupsManager $groupManager) {
        $this->groupManager = $groupManager;
    }

    public function injectGroup2UserManager(\App\Models\Users2Groups $group2User) {
        $this->group2User = $group2User;
    }

    public function injectForumsManager(\App\Models\ForumsManager $forumsManager) {
        $this->forumsManager = $forumsManager;
    }

    public function injectUsers2Forums(\App\Models\Users2Forums $users2Forums) {
        $this->users2Forums = $users2Forums;
    }

    public function renderChangePassword($user_id) {
        
    }

    public function renderEdit($id = null) {
        parent::renderEdit($id);

        $this->template->groups = $this->groupManager->getAllCached();
        $this->template->myGroups = array_values($this->group2User->getByLeft($id));

        $this->template->forums = $this->forumsManager->createForums($this->forumsManager->getAllCached(), 0);
        $this->template->myForums = array_values($this->users2Forums->getByLeft($id));
    }

    protected function createComponentEditForm() {
        $form = $this->getBootStrapForm();
        $form->setTranslator($this->getAdminTranslator());

        $form->addGroup('user_data');
        $form->addText('user_name', 'User name:')->setRequired(true);
        $form->addEmail('user_email', 'User mail:')->setRequired(true);
        $form->addGroup('user_settings');
        $form->addSelect('user_role_id', 'User role:', \App\Authenticator::ROLES)->setTranslator(null);
        $form->addSelect('user_lang_id', 'User language:', $this->languagesManager->getAllForSelect());
        $form->addTextArea('user_signature', 'User signature:');
        //$form->addUpload('user_avatar', 'User avatar:');

        $form->addCheckbox('user_active', 'User active:');

        return $this->addSubmitB($form);
    }

    protected function createComponentChangePasswordControl() {
        return new \App\Controls\ChangePasswordControl($this->getManager(), $this->getAdminTranslator(), true);
    }

    protected function createComponentGroupFrom() {
        $form = new \App\Controls\BootstrapForm();

        $form->addSubmit('send_group', 'Send');
        $form->onSuccess[] = [$this, 'groupSuccess'];

        return $form;
    }

    public function groupSuccess(\Nette\Application\UI\Form $form, \Nette\Utils\ArrayHash $values) {
        $groups = $form->getHttpData($form::DATA_TEXT, 'group[]');
        $user_id = $this->getParameter('id');

        $this->group2User->addByLeft((int) $user_id, array_values($groups));
        $this->flashMessage('Groups saved.', self::FLASH_MESSAGE_SUCCES);
        $this->redirect('User:edit', $user_id);
    }

    public function createComponentForumsForm() {
        $form = new \App\Controls\BootstrapForm();

        $form->addSubmit('send_forum', 'Send');
        $form->onSuccess[] = [$this, 'forumsSuccess'];
        return $form;
    }

    public function forumsSuccess(\Nette\Application\UI\Form $form, \Nette\Utils\ArrayHash $values) {
        $forums = $form->getHttpData($form::DATA_TEXT, 'forums[]');
        $user_id = $this->getParameter('id');

        $this->users2Forums->addByLeft((int) $user_id, array_values($forums));
        $this->flashMessage('Forums saved.', self::FLASH_MESSAGE_SUCCES);
        $this->redirect('User:edit', $user_id);
    }

}
