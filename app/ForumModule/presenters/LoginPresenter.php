<?php

namespace App\ForumModule\Presenters;

use App\Authenticator;
use App\Controls\BootstrapForm;
use App\Models\SessionsManager;
use App\Presenters\Base\BasePresenter;
use Nette\Application\UI\Form;
use Nette\Security\AuthenticationException;
use Nette\Utils\ArrayHash;

/**
 * Description of LoginPresenter
 *
 * @author rendi
 */
class LoginPresenter extends BasePresenter
{
    /**
     * @persistent
     * @var string $backlink
     */
    public $backlink = '';
    
    /**
     * @var Authenticator $authenticator
     */
    private $authenticator;
    
    /**
     * @var SessionsManager $sessionManager
     * @inject
     */
    public $sessionManager;

    /**
     * LoginPresenter constructor.
     *
     * @param Authenticator $authenticator
     */
    public function __construct(Authenticator $authenticator)
    {
        parent::__construct();

        $this->authenticator = $authenticator;
    }
    
    /**
     * 
     * @param mixed $element
     */
    public function checkRequirements($element)
    {       
        $this->getUser()->getStorage()->setNamespace('frontend');
        
        parent::checkRequirements($element);
    }
    
    /**
     * start up method
     * sets Authenticator
     */
    public function startup()
    {
        parent::startup();

        $this->getUser()->setAuthenticator($this->authenticator);
    }    
    
    /**
     * before render method
     * sets translator
     */
    public function beforeRender()
    {
        parent::beforeRender();

        $this->template->setTranslator($this->translatorFactory->forumTranslatorFactory());
    }

    /**
     * @param Form      $form
     * @param ArrayHash $values
     */
    public function loginForumSuccess(Form $form, ArrayHash $values)
    {
        try {
            $user = $this->getUser();
            $user->login(
                $values->user_name,
                $values->user_password
            );
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
                'Successfully logged in.',
                self::FLASH_MESSAGE_SUCCESS
            );
            $this->restoreRequest($this->backlink);
            $this->redirect('Index:default');
        } catch (AuthenticationException $e) {
            $this->flashMessage(
                $e->getMessage(),
                self::FLASH_MESSAGE_DANGER
            );
        }
    }

    /**
     * @return BootstrapForm
     */
    protected function createComponentLoginForm()
    {
        $form = $this->getBootstrapForm();
        $form->setTranslator($this->translatorFactory->forumTranslatorFactory());

        $form->addText('user_name', 'Login:');
        $form->addPassword('user_password', 'Password:');
        $form->addSubmit('send');
        $form->onSuccess[] = [
            $this,
            'loginForumSuccess'
        ];

        return $form;
    }
}
