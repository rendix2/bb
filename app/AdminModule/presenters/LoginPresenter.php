<?php

namespace App\AdminModule\Presenters;

/**
 * Description of LoginPresenter
 *
 * @author rendi
 */
class LoginPresenter extends \App\Presenters\Base\ManagerPresenter
{
    /**
     *
     * @var \App\Translator $translator
     */
    public $translator;

    public function __construct(\App\Models\UsersManager $manager, \App\Controls\AppDir $appDir)
    {
        parent::__construct($manager);
        
        $this->translator = $this->translatorFactory->adminTranslatorFactory();
    }
    
    public function startup()
    {
        parent::startup();
        
        $this->template->setTranslator($this->translator);
    }

    protected function createComponentAdminLoginForm()
    {
        $form = new \App\Controls\BootstrapForm();
        $form->setTranslator($this->translator);
        
        $form->addText('user_name', 'Login:');
        $form->addPassword('user_password', 'Password:');
        $form->addSubmit('send', 'Login');
        $form->onSuccess[] = [$this, 'adminLoginFormSuccess'];
        
        return $form;
    }
    
    public function adminLoginFormSuccess(\Nette\Application\UI\Form $form, \Nette\Utils\ArrayHash $values)
    {
        // check if user is admin    
    }
}
