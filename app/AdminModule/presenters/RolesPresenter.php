<?php

namespace App\AdminModule\Presenters;

/**
 * Description of RolesPresenter
 *
 * @author rendi
 */
class RolesPresenter extends Base\AdminPresenter {

    private $userManager;

    public function __construct(\App\Models\RolesManager $manager) {
        parent::__construct($manager);

        $this->setTitle('Role');
    }

    public function injectUserManager(\App\Models\UsersManager $userManager) {
        $this->userManager = $userManager;
    }

    public function renderEdit($id = null) {
        parent::renderEdit($id);

        $this->template->userCount = $this->userManager->getCountByRoleId($id);
    }

    protected function createComponentEditForm() {
        $form = $this->getBootStrapForm();
        $form->setTranslator($this->getAdminTranslator());

        $form->addText('role_name', 'Role name:')->setRequired(true);


        return $this->addSubmitB($form);
    }

    public function renderUsers($role_id) {
        $users = $this->userManager->getByRoleId($role_id);

        if (!$users) {
            $this->flashMessage('No users in this role', self::FLASH_MESSAGE_WARNING);
        }

        $this->template->items = $users;
    }

}
