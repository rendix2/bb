<?php

namespace App\ForumModule\Presenters;

use Nette\Application\UI\Form;
use Nette\Utils\ArrayHash;

/**
 * Description of RegisterPresenter
 *
 * @author rendi
 */
class RegisterPresenter extends \App\Presenters\Base\BasePresenter
{
    /**
     * @var \Nette\Localization\ITranslator $translator 
     */
    private $translator;   
    
    /**
     * @var \App\Models\LanguagesManager $languageManager 
     */
    private $languageManager;

    public function __construct(\App\Models\LanguagesManager $languageManger)
    {
        parent::__construct();
        
        $this->languageManager = $languageManger;
    }
    
    public function startup() {
        parent::startup();
        
        $this->translator = $this->translatorFactory->forumTranslatorFactory();
                
        $this->template->setTranslator($this->translator);        
    }

    protected function createComponentRegisterUser()
    {
        $form = new \App\Controls\BootstrapForm();
        $form->addText('user_name', 'User name:');
        $form->addPassword('user_password', 'User password:');
        $form->addPassword('user_password2', 'User password for check:');
        $form->addEmail('user_email', 'User email:');
        $form->addSelect('user_lang_id', 'User lang:', $this->languageManager->getAllPairsCached('lang_name'));        
        $form->addSubmit('send', 'User register');
        
        $form->onValidate[] = [$this, 'registerOnValidate'];
        $form->onSuccess[]  = [$this, 'registerUserSuccess'];
        
        return $form;
    }

    public function registerOnValidate(Form $form, ArrayHash $values)
    {
        
    }

    /**
     * 
     * @param Form $form
     * @param ArrayHash $values
     */
    public function registerUserSuccess(Form $form, ArrayHash $values)
    {
        
    }

    public function renderDefault()
    {   
    }
}
