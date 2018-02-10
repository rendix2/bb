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

    private $users2Groups;


    public function __construct(\App\Models\GroupsManager $manager) {
        parent::__construct($manager);
    }
    
    public function injectUsers2Groups(\App\Models\Users2Groups $users2Groups){
    $this->users2Groups = $users2Groups;
    }
    
    public function renderEdit($id = null) {
        parent::renderEdit($id);
        
        $this->template->countOfUsers = $this->users2Groups->getCountByRight($id);
    }

    protected function createComponentEditForm() {
        $form = $this->getBootStrapForm();
        $form->setTranslator($this->getAdminTranslator());
        
        $form->addText('group_name', 'Group name:')->setRequired(true);
        return $this->addSubmitB($form);
    }

}
