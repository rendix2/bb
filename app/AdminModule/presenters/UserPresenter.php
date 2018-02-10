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

    /**
     *
     * @var \App\Models\GroupsManager $groupManager
     */
    private $groupManager;

    public function __construct(\App\Models\UsersManager $manager) {
        parent::__construct($manager);
    }

    public function renderChangePassword($user_id) {
        
    }

    public function renderEdit($id = null) {
        parent::renderEdit($id);

        $this->template->groups   = $this->groupManager->getAll();
        $this->template->myGroups = $this->groupManager->getGrupsByUserId($id);
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

    protected function createComponentEditForm() {
        $form = $this->getBootStrapForm();
        $form->setTranslator($this->getAdminTranslator());

        $form->addGroup('user_data');
        $form->addText('user_name', 'User name:')->setRequired(true);
        $form->addEmail('user_email', 'User mail:')->setRequired(true);
        $form->addGroup('user_settings');
        $form->addSelect('user_role_id', 'User role:', $this->rolesManager->getForSelect())->setTranslator(null)->setRequired(true);
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
        $data = [];

        foreach ($groups as $group) {
            $data['group_id'][] = (int)$group;
            $data['user_id'][] = (int)$user_id;
        }
        
        $this->groupManager->deleteRelationByUserId($user_id);
        $this->groupManager->addRealtion($data);
        
        $this->flashMessage('Groups saved.', self::FLASH_MESSAGE_SUCCES);
        $this->renderChangePassword('User:edit', $user_id);
        
    }

}
