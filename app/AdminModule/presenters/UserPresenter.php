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

    public function __construct(\App\Models\UsersManager $manager) {
        parent::__construct($manager);
    }
    
    public function injectRolesManager(\App\Models\RolesManager $rolesManager){
        $this->rolesManager = $rolesManager;
    }
    
    public function injectLanguagesManager(\App\Models\LanguagesManager $languagesManager){
        $this->languagesManager = $languagesManager;
    }

    protected function createComponentEditForm() {
        $form = $this->getBootStrapForm();
        $form->setTranslator($this->getAdminTranslator());

        $form->addText('user_name', 'User name:')->setRequired(true);
        $form->addEmail('user_email', 'User mail:')->setRequired(true);
        $form->addSelect('user_role_id', 'User role:', $this->rolesManager->getForSelect())->setTranslator(null)->setRequired(true);
        $form->addTextArea('user_signature', 'User signature:');        
        //$form->addUpload('user_avatar', 'User avatar:');
        $form->addSelect('user_lang_id', 'User language:', $this->languagesManager->getAllForSelect());
        $form->addCheckbox('user_active', 'User active:');
        
        
        return $this->addSubmitB($form);
    }

}
