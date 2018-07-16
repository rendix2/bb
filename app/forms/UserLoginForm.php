<?php

namespace App\Forms;

use App\Authenticator;
use App\Controls\BootstrapForm;
use App\Services\TranslatorFactory;
use App\Models\SessionsManager;
use App\Presenters\Base\BasePresenter;
use Nette\Security\User;
use Nette\Application\UI\Form;
use Nette\Utils\ArrayHash;
use Nette\Http\Session;

/**
 * Description of UserLoginForm
 *
 * @author rendi
 */
class UserLoginForm extends \Nette\Application\UI\Control
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
     * @var Nette\Security\User $user 
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
     * @var Nette\Http\Session $session
     */
    private $session;

    /**
     * 
     * @param TranslatorFactory $translatorFactory
     * @param User ¨            $user
     */
    public function __construct(TranslatorFactory $translatorFactory, User $user, SessionsManager $sessionsManager, Authenticator $authenticator, Session $session)
    {
        parent::__construct();
        
        $this->translatorFactory = $translatorFactory;
        $this->user              = $user;
        $this->sessionsManager  = $sessionsManager;
        $this->authenticator     = $authenticator;
        $this->session           = $session;
    }
    
    public function render()
    {
        $sep = DIRECTORY_SEPARATOR;
        
        $this->template->setFile(__DIR__ . $sep . 'templates'. $sep .'userLoginForm'.$sep.'userLoginForm.latte');
        $this->template->setTranslator($this->translatorFactory->forumTranslatorFactory());
            
        $this->template->render();
    }

    /**
     * @return BootstrapForm
     */
    protected function createComponentLoginForm()
    {
        $form = BootstrapForm::create();
        $form->setTranslator($this->translatorFactory->forumTranslatorFactory());

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
            $user->setAuthenticator($this->authenticator);

            $user->login(
                $values->user_name,
                $values->user_password
            );
            
            $addArray =
                [
                    'session_key'     => $this->session->getId(),
                    'session_user_id' => $this->user->getId(),
                    'session_from'    => time()
                ];
            
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
