<?php

namespace App\AdminModule\Presenters;

/**
 * Description of RolesPresenter
 *
 * @author rendi
 */
class RolesPresenter extends Base\AdminPresenter {

    public function __construct(\App\Models\RolesManager $manager) {
        parent::__construct($manager);

        $this->setTitle('Role');
    }

    protected function createComponentEditForm() {
        $form = $this->getBootStrapForm();
        $form->setTranslator($this->getAdminTranslator());

        $form->addText('role_name', 'Role name:')->setRequired(true);


        return $this->addSubmitB($form);
    }

}
