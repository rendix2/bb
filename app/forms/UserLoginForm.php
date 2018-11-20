<?php

namespace App\Forms;

use App\Authenticator;
use App\Controls\BootstrapForm;
use App\Models\SessionsManager;
use App\Presenters\Base\BasePresenter;
use App\Services\TranslatorFactory;
use Nette\Application\UI\Control;
use Nette\Application\UI\Form;
use Nette\Http\Session;
use Nette\Security\AuthenticationException;
use Nette\Security\User;
use Nette\Utils\ArrayHash;

/**
 * Description of UserLoginForm
 *
 * @author rendix2
 * @package App\Forms
 */
class UserLoginForm extends Control
{
    /**
     * @var string $backlink
     * @persistent
     */
    public $backlink = '';
    
    /**
     *
     * @var TranslatorFactory $translatorFactory
     */
    private $translatorFactory;
    
    /**
     *
     * @var User $user
     */
    private $user;
    
    /**
     *
     * @var SessionsManager $sessionsManager
     */
    private $sessionsManager;
    
    /**
     * @var Authenticator $authenticator
     */
    private $authenticator;
    
    /**
     * @var Session $session
     */
    private $session;

    /**
     *
     * @param TranslatorFactory $translatorFactory
     * @param User ¨            $user
     * @param SessionsManager   $sessionsManager
     * @param Authenticator     $authenticator
     * @param Session           $session
     */
    public function __construct(
        TranslatorFactory $translatorFactory,
        User              $user,
        SessionsManager   $sessionsManager,
        Authenticator     $authenticator,
        Session           $session
    ) {
        parent::__construct();
        
        $this->translatorFactory = $translatorFactory;
        $this->user              = $user;
        $this->sessionsManager   = $sessionsManager;
        $this->authenticator     = $authenticator;
        $this->session           = $session;
    }
    
    /**
     * 
     */
    public function __destruct()
    {
        $this->backlink          = null;
        $this->translatorFactory = null;
        $this->user              = null;
        $this->sessionsManager   = null;
        $this->authenticator     = null;
        $this->session           = null;
    }

    public function render()
    {
        $this['loginForm']->render();
    }

    /**
     * @return BootstrapForm
     */
    protected function createComponentLoginForm()
    {
        $form = BootstrapForm::create();
        $form->setTranslator($this->translatorFactory->createForumTranslatorFactory());

        $form->addText('user_name', 'Login:');
        $form->addPassword('user_password', 'Password:');
        $form->addSubmit('send', 'Log in');
        $form->onSuccess[] = [
            $this,
            'loginForumSuccess'
        ];

        return $form;
    }
    
    /**
     * @param Form      $form
     * @param ArrayHash $values
     */
    public function loginForumSuccess(Form $form, ArrayHash $values)
    {
        try {
            $user = $this->user;

            $user->login(
                $values->user_name,
                $values->user_password
            );
            
            $addArray =
                [
                    'session_key'     => $this->session->getId(),
                    'session_user_id' => $this->user->id,
                    'session_from'    => time()
                ];
            
            $this->sessionsManager->delete($user->id);
            $this->sessionsManager->add(ArrayHash::from($addArray));
            $user->setExpiration('1 hour');
            $this->flashMessage(
                'Successfully logged in.',
                BasePresenter::FLASH_MESSAGE_SUCCESS
            );
            $this->presenter->restoreRequest($this->backlink);
            $this->presenter->redirect('Index:default');
        } catch (AuthenticationException $e) {
            $this->presenter->flashMessage(
                $e->getMessage(),
                BasePresenter::FLASH_MESSAGE_DANGER
            );
        }
    }
}
