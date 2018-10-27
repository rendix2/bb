<?php

namespace App\AdminModule\Presenters;

use App\Authenticator;
use App\Controls\BootstrapForm;
use App\Forms\UserLoginForm;
use App\Presenters\Base\BasePresenter;
use App\Services\UserLoginFormFactory;
use App\Translator;
use Nette\Application\UI\Form;
use Nette\Security\AuthenticationException;
use Nette\Utils\ArrayHash;
use App\Models\SessionsManager;

/**
 * Description of LoginPresenter
 *
 * @author rendix2
 */
class LoginPresenter extends BasePresenter
{
    /**
     * @persistent
     * @var string $backlink
     */
    public $backlink = '';
    
    /**
     *
     * @var Translator $translator
     */
    private $translator;
    
    /**
     * session manager
     *
     * @var SessionsManager $sessionManager
     * @inject
     */
    public $sessionManager;
    
    /**
     *
     * @var UserLoginFormFactory $userLoginFormFactory
     * @inject
     */
    public $userLoginFormFactory;

    /**
     * 
     */
    public function __destruct()
    {
        $this->backlink             = null;
        $this->translator           = null;
        $this->sessionManager       = null;
        $this->userLoginFormFactory = null;
        
        parent::__destruct();
    }

    /**
     *
     * @param type $element
     */
    public function checkRequirements($element)
    {
        $this->getUser()->getStorage()->setNamespace(self::BECK_END_NAMESPACE);
        
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
     * @return BootstrapForm
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
            
            if (!$this->getUser()->isInRole(\App\Authorization\Authorizator::ROLES[5])) {
                throw new AuthenticationException('You are not admin.');
            }
            
            
            $this->sessionManager->delete($this->getUser()->getId());
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

    /**
     * @return UserLoginForm
     */
    protected function createComponentLoginForm()
    {
        return $this->userLoginFormFactory->create();
    }
}
