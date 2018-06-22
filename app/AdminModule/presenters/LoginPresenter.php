<?php

namespace App\AdminModule\Presenters;

/**
 * Description of LoginPresenter
 *
 * @author rendi
 */
class LoginPresenter extends \App\Presenters\Base\ManagerPresenter
{
    public function __construct(\App\Models\UsersManager $manager)
    {
        parent::__construct($manager);
    }

        protected function createComponentAdminLoginForm()
    {
        $form = new \App\Controls\BootstrapForm();
        $form->addText('user_name', 'User name:');
        $form->addPassword('user_password', 'User password:');
        $form->addSubmit('send', 'Login');
        $form->onSuccess[] = [$this, 'adminLoginFormSuccess'];
        
        return $form;
    }
    
    public function adminLoginFormSuccess(\Nette\Application\UI\Form $form, \Nette\Utils\ArrayHash $values)
    {
        
    }
}
