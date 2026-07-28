<?php

namespace App\Forms;

use App\Authenticator;
use App\Models\SessionManager;
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
    public string $backlink = '';
    
    /**
     *
     * @var TranslatorFactory $translatorFactory
     */
    private TranslatorFactory $translatorFactory;
    
    /**
     *
     * @var User $user
     */
    private User $user;
    
    /**
     *
     * @var SessionManager $sessionsManager
     */
    private SessionManager $sessionsManager;
    
    /**
     * @var Authenticator $authenticator
     */
    private Authenticator $authenticator;
    
    /**
     * @var Session $session
     */
    private Session $session;

    /**
     * UserLoginForm constructor.
     *
     * @param TranslatorFactory $translatorFactory
     * @param User              $user
     * @param SessionManager   $sessionsManager
     * @param Authenticator     $authenticator
     * @param Session           $session
     */
    public function __construct(
        TranslatorFactory $translatorFactory,
        User              $user,
        SessionManager   $sessionsManager,
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
     *  UserLoginForm render.
     */
    public function render(): void
    {
        $this['loginForm']->render();
    }


    protected function createComponentLoginForm(): \Contributte\FormsBootstrap\BootstrapForm
    {
        $form = new \Contributte\FormsBootstrap\BootstrapForm();
        $form->setTranslator($this->translatorFactory->getForumTranslator());

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
    public function loginForumSuccess(Form $form, ArrayHash $values): void
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
                    'session_user_id' => $user->getId(),
                    'session_from'    => time()
                ];
            
            $this->sessionsManager->delete($user->getId());
            $this->sessionsManager->add(ArrayHash::from($addArray));
            $user->setExpiration('1 hour');
            $this->flashMessage(
                'Successfully logged in.',
                BasePresenter::FLASH_MESSAGE_SUCCESS
            );
            $this->getPresenter()->restoreRequest($this->backlink);
            $this->getPresenter()->redirect('Index:default');
        } catch (AuthenticationException $e) {
            $this->getPresenter()->flashMessage(
                $e->getMessage(),
                BasePresenter::FLASH_MESSAGE_DANGER
            );
        }
    }
}
