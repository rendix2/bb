<?php

namespace App\AdminModule\Presenters;

/**
 * Description of LanguagePresenter
 *
 * @author rendi
 */
class LanguagePresenter extends Base\AdminPresenter {
    
    public function __construct(\App\Models\LanguagesManager $manager) {
        parent::__construct($manager);
    }

    protected function createComponentEditForm() {
        $form = $this->getBootStrapForm();       
        $form->setTranslator($this->getAdminTranslator());
        
        $form->addText('lang_name', 'Language name:')->setRequired();
        
        return $this->addSubmitB($form);
    }
}
