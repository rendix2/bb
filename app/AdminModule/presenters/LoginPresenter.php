<?php

namespace App\AdminModule\Presenters;

use App\Authorization\Authorizator;
use App\Controls\BootstrapForm;
use App\Forms\UserLoginForm;
use App\Models\Entity\SessionEntity;
use App\Models\SessionsManager;
use App\Presenters\Base\BasePresenter;
use App\Services\UserLoginFormFactory;
use App\Translator;
use Nette\Application\UI\Form;
use Nette\Security\AuthenticationException;
use Nette\Utils\ArrayHash;

/**
 * Description of LoginPresenter
 *
 * @author rendix2
 * @package App\AdminModule\Presenters
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
     * @param mxied $element
     */
    public function checkRequirements($element)
    {
        $this->user->getStorage()->setNamespace(self::BECK_END_NAMESPACE);
        
        parent::checkRequirements($element);
    }

    /**
     * startup method
     */
    public function startup()
    {
        parent::startup();
        
        $this->translator = $this->translatorFactory->createAdminTranslatorFactory();
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
     */
    public function adminLoginFormSuccess(Form $form, ArrayHash $values)
    {
        // check if user is admin
        try {
            $user = $this->user;
            
            $user->login(
                $values->user_name,
                $values->user_password
            );
            
            if (!$user->isInRole(Authorizator::ROLES[5])) {
                throw new AuthenticationException('You are not admin.');
            }
            
            $sessionEntity = new SessionEntity();
            $sessionEntity->setSession_key($this->session->getId())
                          ->setSession_user_id($user->id)
                          ->setSession_from(time());
            
            $this->sessionManager->deleteByUser($user->id);
            $this->sessionManager->add($sessionEntity->getArrayHash());
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
