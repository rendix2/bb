<?php

namespace App\AdminModule\Presenters;

use App\Models\RolesManager;
use App\Models\UsersManager;

/**
 * Description of RolesPresenter
 *
 * @author rendi
 */
class RolesPresenter extends Base\AdminPresenter
{

    /**
     * @var UsersManager $userManager
     */
    private $userManager;

    /**
     * RolesPresenter constructor.
     *
     * @param RolesManager $manager
     */
    public function __construct(RolesManager $manager)
    {
        parent::__construct($manager);

        $this->setTitle('Role');
    }

    /**
     * @param UsersManager $userManager
     */
    public function injectUserManager(UsersManager $userManager)
    {
        $this->userManager = $userManager;
    }

    /**
     * @param null $id
     */
    public function renderEdit($id = null)
    {
        parent::renderEdit($id);

        $this->template->userCount = $this->userManager->getCountByRoleId($id);
    }

    /**
     * @param $role_id
     */
    public function renderUsers($role_id)
    {
        $users = $this->userManager->getByRoleId($role_id);

        if (!$users) {
            $this->flashMessage('No users in this role', self::FLASH_MESSAGE_WARNING);
        }

        $this->template->items = $users;
    }

    /**
     * @return \App\Controls\BootStrapForm
     */
    protected function createComponentEditForm()
    {
        $form = $this->getBootStrapForm();
        $form->setTranslator($this->getAdminTranslator());

        $form->addText('role_name', 'Role name:')->setRequired(true);


        return $this->addSubmitB($form);
    }

}
