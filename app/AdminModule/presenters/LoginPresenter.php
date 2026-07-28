<?php

namespace App\AdminModule\Presenters;

use App\Authorization\Authorizator;
use App\Forms\UserLoginForm;
use App\Models\SessionManager;
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
    public string $backlink = '';
    
    /**
     *
     * @var Translator $translator
     */
    private Translator $translator;
    
    /**
     * session manager
     *
     * @var SessionManager $sessionManager
     * @inject
     */
    public SessionManager $sessionManager;
    
    /**
     *
     * @var UserLoginFormFactory $userLoginFormFactory
     * @inject
     */
    public UserLoginFormFactory $userLoginFormFactory;

    /**
     *
     * @param mixed $element
     */
    public function checkRequirements($element): void
    {
        $this->getUser()->getStorage()->setNamespace(self::BACK_END_NAMESPACE);
        
        parent::checkRequirements($element);
    }

    /**
     * LoginPresenter startup.
     */
    public function startup()
    {
        parent::startup();
        
        $this->translator = $this->translatorFactory->getAdminTranslator();
        $this->getTemplate()->setTranslator($this->translator);
    }

    protected function createComponentAdminLoginForm(): \Contributte\FormsBootstrap\BootstrapForm
    {
        $form = new \Contributte\FormsBootstrap\BootstrapForm();
        
        $form->addText('username', 'Login:');
        $form->addPassword('password', 'Password:');
        $form->addSubmit('send', 'Login');

        $form->onSuccess[] = [$this, 'adminLoginFormSuccess'];
        
        return $form;
    }

    /**
     * @param Form      $form
     * @param ArrayHash $values
     *
     */
    public function adminLoginFormSuccess(Form $form, ArrayHash $values): void
    {
        // check if user is admin
        try {
            $user = $this->getUser();
            
            $user->login(
                $values->user_name,
                $values->user_password
            );
            
            if (!$user->isInRole(Authorizator::ROLES[5])) {
                throw new AuthenticationException('You are not admin.');
            }
            
            $sessionEntity = new \App\Model\Entity\SessionEntity();
            $sessionEntity->setSession_key($this->getSession()->getId())
                          ->setSession_user_id($user->getId())
                          ->setSession_from(time());
            
            $this->sessionManager->deleteByUser($user->getId());
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
    protected function createComponentLoginForm(): UserLoginForm
    {
        return $this->userLoginFormFactory->create();
    }
}
