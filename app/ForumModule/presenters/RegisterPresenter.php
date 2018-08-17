<?php

namespace App\ForumModule\Presenters;

use App\Controls\BootstrapForm;
use App\Models\LanguagesManager;
use App\Models\UsersManager;
use App\Presenters\Base\BasePresenter;
use Nette\Application\UI\Form;
use Nette\Utils\ArrayHash;
use Nette\Localization\ITranslator;

/**
 * Description of RegisterPresenter
 *
 * @author rendix2
 */
class RegisterPresenter extends BasePresenter
{
    /**
     * @var ITranslator $translator 
     */
    private $translator;   
    
    /**
     * @var LanguagesManager $languageManager 
     */
    private $languageManager;
    
    /**
     *
     * @var UsersManager $usersManager
     */
    private $usersManager;

    /**
     * RegisterPresenter constructor.
     *
     * @param LanguagesManager $languageManger
     * @param UsersManager     $usersManager
     */
    public function __construct(LanguagesManager $languageManger, UsersManager $usersManager)
    {
        parent::__construct();
        
        $this->languageManager = $languageManger;
        $this->usersManager    = $usersManager;
    }
    
    public function startup()
    {
        parent::startup();
        
        $this->translator = $this->translatorFactory->forumTranslatorFactory();
                
        $this->template->setTranslator($this->translator);
    }

    /**
     * @return BootstrapForm
     */
    protected function createComponentRegisterUser()
    {
        $form = $this->getBootstrapForm();
        $form->setTranslator($this->translator);
        
        $form->addText('user_name', 'User name:')->setRequired(true);
        $form->addPassword('user_password', 'User password:')->setRequired(true);
        $form->addPassword('user_password2', 'User password for check:')->setRequired(true);
        $form->addEmail('user_email', 'User email:')->setRequired(true);
        $form->addSelect('user_lang_id', 'User lang:', $this->languageManager->getAllPairsCached('lang_name'));
        $form->addReCaptcha('user_captcha', 'User captcha:', 'Please prove you are not robot.');
        $form->addSubmit('send', 'User register');
        
        $form->onValidate[] = [$this, 'registerOnValidate'];
        $form->onSuccess[]  = [$this, 'registerUserSuccess'];
        
        return $form;
    }

    /**
     *
     * @param Form      $form
     * @param ArrayHash $values
     */
    public function registerOnValidate(Form $form, ArrayHash $values)
    {
        $user_name = $this->usersManager->getByName($values->user_name);
        
        if ($user_name) {
            $form->addError('User name is already taken.');
        }
        
        $user_email = $this->usersManager->getByEmail($values->user_email);
        
        if ($user_email) {
            $form->addError('User email is already taken.');
        }
    }

    /**
     *
     * @param Form $form
     * @param ArrayHash $values
     */
    public function registerUserSuccess(Form $form, ArrayHash $values)
    {
        unset($values->user_password2);
        $values->user_password      = \Nette\Security\Passwords::hash($values->user_password);
        $values->user_register_time = time();
        $values->user_role_id       = 1;
                
        $res = $this->usersManager->add(ArrayHash::from($values));
        
        if ($res) {
            $this->flashMessage('User was added.', self::FLASH_MESSAGE_SUCCESS);
        } else {
            $this->flashMessage('User was not added.', self::FLASH_MESSAGE_DANGER);
        }
        
        $this->redirect('Login:default');
    }

    /**
     * 
     */
    public function renderDefault()
    {
    }
}
