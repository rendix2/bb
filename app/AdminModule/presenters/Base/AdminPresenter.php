<?php

namespace App\AdminModule\Presenters\Base;

/**
 * Description of AdminPresenter¨
 *
 * @author rendi
 */
abstract class AdminPresenter extends \App\Presenters\crud\CrudPresenter {

    private $adminTtranslator;
    
    private $form;
    
    private $bootStrapForm;


    public function __construct(\App\Models\Crud\CrudManager $manager) {
        parent::__construct($manager);
        
        $this->form             = new \Nette\Application\UI\Form();
        $this->bootStrapForm    = new \App\Controls\BootstrapForm();          
    }
    
    public function getAdminTranslator(){
        return $this->adminTtranslator;                
    }
    
    /**
     * 
     * @return \Nette\Application\UI\Form
     */
    public function getForm() {
        return $this->form;
    }
    
    /**
     * 
     * @return \App\Controls\BootstrapForm
     */
    public function getBootStrapForm(){
        return $this->bootStrapForm;
    }    

    public function startup() {
        parent::startup();

        $user = $this->getUser();

        if (!$user->isLoggedIn()) {
            $this->error('You are not logged in.');
        }

        if (!$user->isInRole('Admin')) {
            $this->error('You are not admin.');
        }              
    }
    
    public function beforeRender() {
        parent::beforeRender();
        
        $this->template->setTranslator($this->adminTtranslator = new \App\Translator('Admin',$this->getUser()->getIdentity()->getData()['lang_file_name']));
    }

}
