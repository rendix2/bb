<?php

namespace App\AdminModule\Presenters;

use Nette\Application\UI\Form;
use Nette\Security\AuthenticationException;
use Nette\Utils\ArrayHash;
use App\Models\SessionsManager;

/**
 * Description of LoginPresenter
 *
 * @author rendi
 */
class LoginPresenter extends \App\Presenters\Base\BasePresenter
{
    /**
     * @persistent
     * @var string $backlink
     */
    public $backlink = '';
    
    /**
     *
     * @var \App\Translator $translator
     */
    public $translator;
    
    /**
     * session manager
     *
     * @var SessionsManager $sessionManager
     * @inject
     */
    public $sessionManager; 
    
    /**
     *
     * @var \App\Services\UserLoginFormFactory $userLoginFormFactory
     * @inject
     */
    public $userLoginFormFactory;


    /**
     * 
     * @param type $element
     */
    public function checkRequirements($element)
    {
        $this->getUser()->getStorage()->setNamespace('beckend'); 
        
        parent::checkRequirements($element);       
    }

    /**
     * startup method
     */
    public function startup()
    {
        parent::startup();
        
        $this->translator = $this->translatorFactory->adminTranslatorFactory();
        $this->template->setTranslator($this->translator);
    }

    /**
     * @return \App\Controls\BootstrapForm
     */
    protected function createComponentAdminLoginForm()
    {
        $form = $this->getBootstrapForm();
        
        $form->addText('user_name', 'Login:');
        $form->addPassword('user_password', 'Password:');
        $form->addSubmit('send', 'Login');
        $form->onSuccess[] = [$this, 'adminLoginFormSuccess'];
        
        return $form;
    }

    /**
     * @param Form      $form
     * @param ArrayHash $values
     *
     * @throws AuthenticationException
     */
    public function adminLoginFormSuccess(Form $form, ArrayHash $values)
    {
        // check if user is admin
        try {
            $user = $this->getUser();
            $user->login(
                $values->user_name,
                $values->user_password
            );
            
            if (!$this->getUser()->isInRole(\App\Authenticator::ROLES[5])) {
              throw new AuthenticationException('You are not admin.');
            }
            
            $this->sessionManager->add(
                ArrayHash::from(
                    [
                        'session_key'     => $this->getSession()->getId(),
                        'session_user_id' => $this->getUser()->getId(),
                        'session_from'    => time()
                    ]
                )
            );
            $user->setExpiration('1 hour');
            $this->flashMessage(
                'Successfully admin logged in.',
                self::FLASH_MESSAGE_SUCCESS
            );
            $this->restoreRequest($this->backlink);
            $this->redirect(':Admin:Index:default');
        } catch (AuthenticationException $e) {
            $this->flashMessage(
                $e->getMessage(),
                self::FLASH_MESSAGE_DANGER
            );
        }
    }
    
    protected function createComponentLoginForm() 
    {
        return $this->userLoginFormFactory->create();
    }
}
