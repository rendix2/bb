<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\AdminModule\Presenters;

/**
 * Description of GroupPresenter
 *
 * @author rendi
 */
class GroupPresenter extends Base\AdminPresenter {

    public function __construct(\App\Models\GroupsManager $manager) {
        parent::__construct($manager);
    }

    protected function createComponentEditForm() {
        $form = $this->getBootStrapForm();
        $form->setTranslator($this->getAdminTranslator());
        
        $form->addText('group_name', 'Group name:')->setRequired(true);
        return $this->addSubmitB($form);
    }

}
